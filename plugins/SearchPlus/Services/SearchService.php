<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SearchPlus\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Product;
use Plugin\SearchPlus\Models\SearchKeyword;
use Plugin\SearchPlus\Models\Synonym;

class SearchService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function driver(): string
    {
        return (string) plugin_setting('search_plus', 'driver', 'database');
    }

    public function limit(): int
    {
        return max(1, min((int) plugin_setting('search_plus', 'limit', 20), 100));
    }

    protected function fallbackHot(): bool
    {
        return (bool) plugin_setting('search_plus', 'fallback_hot', true);
    }

    /**
     * 执行搜索，返回 [results, fallback, expanded]。
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['results' => [], 'fallback' => false, 'terms' => []];
        }

        $terms = $this->expandSynonyms($query);

        $ids = $this->driver() === 'meilisearch'
            ? $this->searchMeili($query)
            : $this->searchDatabase($terms);

        $this->recordKeyword($query, count($ids));

        $results = $this->formatByIds($ids);
        $fallback = false;

        if (empty($results) && $this->fallbackHot()) {
            $results = $this->hot();
            $fallback = true;
        }

        return ['results' => $results, 'fallback' => $fallback, 'terms' => $terms];
    }

    /**
     * 同义词扩展：把查询词扩展为同义词组中的全部词。
     */
    public function expandSynonyms(string $query): array
    {
        $terms = [$query];
        $groups = Synonym::query()->where('is_active', true)->pluck('terms');

        foreach ($groups as $group) {
            $words = array_filter(array_map('trim', explode(',', (string) $group)));
            foreach ($words as $w) {
                if (mb_stripos($query, $w) !== false || mb_stripos($w, $query) !== false) {
                    $terms = array_merge($terms, $words);
                    break;
                }
            }
        }

        return array_values(array_unique(array_filter($terms)));
    }

    protected function searchDatabase(array $terms): array
    {
        $locale = app()->getLocale();

        $q = DB::table('product_translations as pt')
            ->join('products as p', 'p.id', '=', 'pt.product_id')
            ->where('p.active', 1)
            ->where('pt.locale', $locale)
            ->where(function ($w) use ($terms) {
                foreach ($terms as $t) {
                    $like = '%'.$t.'%';
                    $w->orWhere('pt.name', 'like', $like)
                        ->orWhere('pt.summary', 'like', $like);
                }
            })
            ->orderByDesc('p.sales')
            ->limit($this->limit())
            ->pluck('p.id')
            ->all();

        // 兼容未配置当前 locale 翻译的情况：回退忽略 locale 再查一次
        if (empty($q)) {
            $q = DB::table('product_translations as pt')
                ->join('products as p', 'p.id', '=', 'pt.product_id')
                ->where('p.active', 1)
                ->where(function ($w) use ($terms) {
                    foreach ($terms as $t) {
                        $w->orWhere('pt.name', 'like', '%'.$t.'%');
                    }
                })
                ->orderByDesc('p.sales')
                ->limit($this->limit())
                ->pluck('p.id')
                ->unique()
                ->all();
        }

        return array_map('intval', $q);
    }

    protected function searchMeili(string $query): array
    {
        $host  = rtrim((string) plugin_setting('search_plus', 'meili_host', ''), '/');
        $index = (string) plugin_setting('search_plus', 'meili_index', 'products');
        $key   = (string) plugin_setting('search_plus', 'meili_key', '');
        if ($host === '') {
            return [];
        }

        try {
            $resp = Http::withHeaders(array_filter(['Authorization' => $key ? "Bearer {$key}" : null]))
                ->timeout(10)
                ->post("{$host}/indexes/{$index}/search", [
                    'q'     => $query,
                    'limit' => $this->limit(),
                ]);

            $hits = $resp->json('hits') ?? [];

            return array_map(fn ($h) => (int) ($h['id'] ?? 0), $hits);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 将商品全量推送到 Meilisearch 索引。
     */
    public function reindexMeili(): int
    {
        $host  = rtrim((string) plugin_setting('search_plus', 'meili_host', ''), '/');
        $index = (string) plugin_setting('search_plus', 'meili_index', 'products');
        $key   = (string) plugin_setting('search_plus', 'meili_key', '');
        if ($host === '') {
            throw new \Exception(__('SearchPlus::common.no_meili'));
        }

        $locale = app()->getLocale();
        $count  = 0;

        Product::query()->with('translation')->where('active', 1)->chunk(200, function ($products) use (&$count, $host, $index, $key, $locale) {
            $docs = $products->map(fn ($p) => [
                'id'      => $p->id,
                'name'    => optional($p->translation)->name ?? '',
                'summary' => optional($p->translation)->summary ?? '',
                'sku'     => $p->spu_code,
                'locale'  => $locale,
                'sales'   => (int) $p->sales,
            ])->all();

            Http::withHeaders(array_filter(['Authorization' => $key ? "Bearer {$key}" : null]))
                ->timeout(30)
                ->post("{$host}/indexes/{$index}/documents", $docs);

            $count += count($docs);
        });

        return $count;
    }

    public function recordKeyword(string $keyword, int $results): void
    {
        $keyword = mb_substr(trim($keyword), 0, 100);
        if ($keyword === '') {
            return;
        }

        $row = SearchKeyword::query()->firstOrNew(['keyword' => $keyword]);
        $row->hits = ($row->exists ? (int) $row->hits : 0) + 1;
        $row->results = $results;
        $row->last_at = now();
        $row->save();
    }

    public function hotWords(int $limit = 10): array
    {
        return SearchKeyword::query()
            ->orderByDesc('hits')
            ->limit($limit)
            ->pluck('keyword')
            ->all();
    }

    public function suggest(string $prefix, int $limit = 10): array
    {
        $prefix = trim($prefix);
        if ($prefix === '') {
            return [];
        }

        $fromKeywords = SearchKeyword::query()
            ->where('keyword', 'like', $prefix.'%')
            ->orderByDesc('hits')
            ->limit($limit)
            ->pluck('keyword')
            ->all();

        if (count($fromKeywords) >= $limit) {
            return $fromKeywords;
        }

        $locale = app()->getLocale();
        $fromNames = DB::table('product_translations')
            ->where('locale', $locale)
            ->where('name', 'like', '%'.$prefix.'%')
            ->limit($limit)
            ->pluck('name')
            ->all();

        return array_values(array_unique(array_merge($fromKeywords, $fromNames)));
    }

    public function hot(): array
    {
        $ids = Product::query()
            ->where('active', 1)
            ->orderByDesc('sales')
            ->orderByDesc('viewed')
            ->limit($this->limit())
            ->pluck('id')
            ->all();

        return $this->formatByIds($ids);
    }

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
                'id'           => $p->id,
                'name'         => optional($p->translation)->name ?? '',
                'slug'         => $p->slug,
                'price'        => (float) $p->price,
                'price_format' => currency_format((float) $p->price),
                'image'        => $p->image_url,
            ];
        }

        return $out;
    }
}
