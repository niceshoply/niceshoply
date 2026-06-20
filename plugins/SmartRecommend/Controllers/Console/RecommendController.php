<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmartRecommend\Controllers\Console;

use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Product;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\SmartRecommend\Models\ProductView;

class RecommendController extends BaseController
{
    protected string $modelClass = ProductView::class;

    public function index(): mixed
    {
        $totalViews   = ProductView::query()->count();
        $totalVisitors = ProductView::query()->distinct('visitor_key')->count('visitor_key');

        // 最近热门浏览 TOP 20
        $topRows = ProductView::query()
            ->select('product_id', DB::raw('COUNT(*) as views'))
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        $products = Product::query()
            ->with('translation')
            ->whereIn('id', $topRows->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        $topViewed = $topRows->map(fn ($r) => [
            'id'    => $r->product_id,
            'name'  => optional(optional($products->get($r->product_id))->translation)->name ?? ('#'.$r->product_id),
            'views' => (int) $r->views,
        ]);

        return nice_view('SmartRecommend::console.index', compact('totalViews', 'totalVisitors', 'topViewed'));
    }
}
