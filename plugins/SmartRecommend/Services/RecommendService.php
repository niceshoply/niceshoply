<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmartRecommend\Services;

use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Product;
use Plugin\SmartRecommend\Models\ProductView;

class RecommendService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function limit(): int
    {
        return max(1, min((int) plugin_setting('smart_recommend', 'limit', 10), 50));
    }

    protected function fallbackHot(): bool
    {
        return (bool) plugin_setting('smart_recommend', 'fallback_hot', true);
    }

    /**
     * 记录一次浏览足迹。
     */
    public function recordView(string $visitorKey, int $productId): void
    {
        if ($visitorKey === '' || $productId <= 0) {
            return;
        }

        ProductView::query()->updateOrCreate(
            ['visitor_key' => $visitorKey, 'product_id' => $productId],
            ['updated_at' => now()]
        );

        $this->trim($visitorKey);
    }

    /**
     * 限制每个访客的浏览记录条数。
     */
    protected function trim(string $visitorKey): void
    {
        $keep = max(1, (int) plugin_setting('smart_recommend', 'recent_keep', 50));
        $ids = ProductView::query()
            ->where('visitor_key', $visitorKey)
            ->orderByDesc('updated_at')
            ->pluck('id');

        if ($ids->count() > $keep) {
            ProductView::query()->whereIn('id', $ids->slice($keep)->all())->delete();
        }
    }

    /**
     * 最近浏览。
     */
    public function recentlyViewed(string $visitorKey, ?int $excludeId = null): array
    {
        if ($visitorKey === '') {
            return [];
        }

        $ids = ProductView::query()
            ->where('visitor_key', $visitorKey)
            ->when($excludeId, fn ($q) => $q->where('product_id', '!=', $excludeId))
            ->orderByDesc('updated_at')
            ->limit($this->limit())
            ->pluck('product_id')
            ->all();

        return $this->formatByIds($ids);
    }

    /**
     * 看了又看：基于浏览共现。
     */
    public function viewedAlsoViewed(int $productId): array
    {
        // 浏览过该商品的访客
        $visitorKeys = ProductView::query()
            ->where('product_id', $productId)
            ->limit(2000)
            ->pluck('visitor_key');

        if ($visitorKeys->isEmpty()) {
            return $this->fallback($productId);
        }

        $rows = ProductView::query()
            ->whereIn('visitor_key', $visitorKeys->all())
            ->where('product_id', '!=', $productId)
            ->select('product_id', DB::raw('COUNT(*) as score'))
            ->groupBy('product_id')
            ->orderByDesc('score')
            ->limit($this->limit())
            ->pluck('product_id')
            ->all();

        return $this->mergeWithFallback($rows, $productId);
    }

    /**
     * 买了又买：基于订单商品共现。
     */
    public function boughtTogether(int $productId): array
    {
        // 含该商品的订单
        $orderIds = DB::table('order_items')
            ->where('product_id', $productId)
            ->limit(2000)
            ->pluck('order_id');

        if ($orderIds->isEmpty()) {
            return $this->fallback($productId);
        }

        $rows = DB::table('order_items')
            ->whereIn('order_id', $orderIds->all())
            ->where('product_id', '!=', $productId)
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as score'))
            ->groupBy('product_id')
            ->orderByDesc('score')
            ->limit($this->limit())
            ->pluck('product_id')
            ->all();

        return $this->mergeWithFallback(array_map('intval', $rows), $productId);
    }

    /**
     * 猜你喜欢：按近期浏览品类聚合热销，兜底全站热销。
     */
    public function forYou(string $visitorKey): array
    {
        $viewedIds = $visitorKey !== ''
            ? ProductView::query()->where('visitor_key', $visitorKey)->orderByDesc('updated_at')->limit(20)->pluck('product_id')->all()
            : [];

        if (! empty($viewedIds)) {
            $categoryIds = DB::table('product_categories')
                ->whereIn('product_id', $viewedIds)
                ->pluck('category_id')
                ->unique()
                ->all();

            if (! empty($categoryIds)) {
                $candidateIds = DB::table('product_categories')
                    ->join('products', 'products.id', '=', 'product_categories.product_id')
                    ->whereIn('product_categories.category_id', $categoryIds)
                    ->where('products.active', 1)
                    ->whereNotIn('products.id', $viewedIds)
                    ->orderByDesc('products.sales')
                    ->limit($this->limit())
                    ->pluck('products.id')
                    ->unique()
                    ->all();

                if (! empty($candidateIds)) {
                    return $this->mergeWithFallback($candidateIds, null);
                }
            }
        }

        return $this->hot();
    }

    /**
     * 全站热销。
     */
    public function hot(?int $excludeId = null): array
    {
        $ids = Product::query()
            ->where('active', 1)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderByDesc('sales')
            ->orderByDesc('viewed')
            ->limit($this->limit())
            ->pluck('id')
            ->all();

        return $this->formatByIds($ids);
    }

    protected function fallback(?int $excludeId): array
    {
        return $this->fallbackHot() ? $this->hot($excludeId) : [];
    }

    /**
     * 不足时用热销补齐。
     */
    protected function mergeWithFallback(array $ids, ?int $excludeId): array
    {
        $list = $this->formatByIds($ids);
        if (! $this->fallbackHot() || count($list) >= $this->limit()) {
            return $list;
        }

        $have = array_column($list, 'id');
        if ($excludeId) {
            $have[] = $excludeId;
        }

        foreach ($this->hot($excludeId) as $item) {
            if (count($list) >= $this->limit()) {
                break;
            }
            if (! in_array($item['id'], $have, true)) {
                $list[] = $item;
                $have[] = $item['id'];
            }
        }

        return $list;
    }

    /**
     * 把商品 ID 列表格式化为前台商品卡片（保持入参顺序）。
     */
    protected function formatByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $products = Product::query()
            ->with('translation')
            ->where('active', 1)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $out = [];
        foreach ($ids as $id) {
            $p = $products->get($id);
            if (! $p) {
                continue;
            }
            $out[] = [
                'id'        => $p->id,
                'name'      => optional($p->translation)->name ?? '',
                'slug'      => $p->slug,
                'price'     => (float) $p->price,
                'price_format' => currency_format((float) $p->price),
                'image'     => $p->image_url,
                'sales'     => (int) $p->sales,
            ];
        }

        return $out;
    }
}
