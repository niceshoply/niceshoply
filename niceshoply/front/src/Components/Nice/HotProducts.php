<?php

namespace NiceShoply\Front\Components\Nice;

use Illuminate\View\Component;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\ProductRepo;

class HotProducts extends Component
{
    public array $groups;

    public string $title;

    public function __construct(string $title = '')
    {
        $this->title  = $title ?: trans('front/home.hot_products', [], null) ?: trans('console/setting.hot_products');
        $this->groups = $this->fetchHotProducts();
    }

    private function fetchHotProducts(): array
    {
        $setting = system_setting('home_hot_products', '{}');

        $data = is_array($setting) ? $setting : (json_decode($setting, true) ?: []);

        if (empty($data['categories']) || ! is_array($data['categories'])) {
            return [];
        }

        try {
            $allProductIds = [];
            foreach ($data['categories'] as $group) {
                if (! empty($group['products']) && is_array($group['products'])) {
                    $allProductIds = array_merge($allProductIds, $group['products']);
                }
            }

            if (empty($allProductIds)) {
                return [];
            }

            $products = ProductRepo::getInstance()->withActive()->builder()
                ->whereIn('products.id', array_unique($allProductIds))
                ->get();

            $categoryIds   = array_filter(array_column($data['categories'], 'category_id'));
            $categoryNames = [];
            if (! empty($categoryIds)) {
                $cats = CategoryRepo::getInstance()
                    ->builder(['category_ids' => array_unique($categoryIds)])
                    ->with(['translation'])
                    ->get();
                foreach ($cats as $cat) {
                    $categoryNames[$cat->id] = $cat->fallbackName();
                }
            }

            $result = [];
            foreach ($data['categories'] as $group) {
                if (empty($group['products']) || ! is_array($group['products'])) {
                    continue;
                }
                $catId   = $group['category_id'] ?? 0;
                $catName = $categoryNames[$catId] ?? ($group['category_name'] ?? "Category #{$catId}");

                $catProducts = [];
                foreach ($group['products'] as $pid) {
                    $p = $products->firstWhere('id', $pid);
                    if ($p) {
                        $catProducts[] = $p;
                    }
                }

                if (! empty($catProducts)) {
                    $result[] = [
                        'category_id'   => $catId,
                        'category_name' => $catName,
                        'products'      => $catProducts,
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function render(): mixed
    {
        if (empty($this->groups)) {
            return '';
        }

        return view('components.nice.hot-products');
    }
}
