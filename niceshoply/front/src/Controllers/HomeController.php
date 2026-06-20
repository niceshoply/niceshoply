<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use NiceShoply\Common\Repositories\ArticleRepo;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\ProductRepo;
use NiceShoply\Front\Repositories\HomeRepo;

class HomeController extends Controller
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $bestSeller  = ProductRepo::getInstance()->getBestSellerProducts();
        $newArrivals = ProductRepo::getInstance()->getLatestProducts();
        $tabProducts = [
            ['tab_title' => trans('front/home.bestseller'), 'products' => $bestSeller],
            ['tab_title' => trans('front/home.new_arrival'), 'products' => $newArrivals],
        ];

        $news = ArticleRepo::getInstance()->getLatestArticles();
        $data = [
            'slideshow'       => HomeRepo::getInstance()->getSlideShow(),
            'tab_products'    => $tabProducts,
            'news'            => $news,
            'hot_products'    => $this->getHotProducts(),
            'home_categories' => HomeRepo::getInstance()->getHomeCategories(),
        ];

        $data = fire_hook_filter('home.index.data', $data);

        return nice_view('home', $data);
    }

    /**
     * Get hot products from settings, organized by category
     * Returns array of category groups with their products
     *
     * @return array Array of category groups: [['category_id' => 1, 'category_name' => 'xxx', 'products' => [...]], ...]
     */
    private function getHotProducts(): array
    {
        $hotProductsSetting = system_setting('home_hot_products', '{}');

        if (is_array($hotProductsSetting)) {
            $hotProductsData = $hotProductsSetting;
        } else {
            $hotProductsData = json_decode($hotProductsSetting, true) ?: [];
        }

        if (empty($hotProductsData) || ! isset($hotProductsData['categories']) || ! is_array($hotProductsData['categories'])) {
            return [];
        }

        $categoryGroups = [];

        try {
            $allProductIds = [];
            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (isset($categoryGroup['products']) && is_array($categoryGroup['products'])) {
                    $allProductIds = array_merge($allProductIds, $categoryGroup['products']);
                }
            }

            if (empty($allProductIds)) {
                return [];
            }

            $products = ProductRepo::getInstance()->withActive()->builder()
                ->whereIn('products.id', array_unique($allProductIds))
                ->get();

            $categoryIds = [];
            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (isset($categoryGroup['category_id'])) {
                    $categoryIds[] = $categoryGroup['category_id'];
                }
            }

            $categories = [];
            if (! empty($categoryIds)) {
                $categoryModels = CategoryRepo::getInstance()
                    ->builder(['category_ids' => array_unique($categoryIds)])
                    ->with(['translation'])
                    ->get();
                foreach ($categoryModels as $category) {
                    $categories[$category->id] = $category->fallbackName();
                }
            }

            foreach ($hotProductsData['categories'] as $categoryGroup) {
                if (! isset($categoryGroup['products']) || ! is_array($categoryGroup['products']) || empty($categoryGroup['products'])) {
                    continue;
                }

                $categoryId   = $categoryGroup['category_id'] ?? 0;
                $categoryName = $categories[$categoryId] ?? ($categoryGroup['category_name'] ?? "Category #{$categoryId}");

                $categoryProducts = [];
                foreach ($categoryGroup['products'] as $productId) {
                    $product = $products->firstWhere('id', $productId);
                    if ($product) {
                        $categoryProducts[] = $product;
                    }
                }

                if (! empty($categoryProducts)) {
                    $categoryGroups[] = [
                        'category_id'   => $categoryId,
                        'category_name' => $categoryName,
                        'products'      => $categoryProducts,
                    ];
                }
            }

            return $categoryGroups;
        } catch (\Exception $e) {
            return [];
        }
    }
}
