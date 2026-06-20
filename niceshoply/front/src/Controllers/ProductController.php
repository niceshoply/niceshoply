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
use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\ProductRepo;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Resources\ProductVariable;
use NiceShoply\Common\Resources\SkuListItem;
use NiceShoply\Front\Traits\FilterSidebarTrait;

class ProductController extends Controller
{
    use FilterSidebarTrait;

    /**
     * Product list page with filter support
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        // Use RequestFilterParser to extract filter conditions
        $filterParser = new \NiceShoply\Common\Services\RequestFilterParser;
        $filters      = $filterParser->extractFilters($request, [
            'keyword',
            'sort',
            'order',
            'per_page',
            'price_from',
            'price_to',
            'brand_ids',
            'attribute_values',
            'in_stock',
        ]);

        // Get product list
        $products = ProductRepo::getInstance()->getFrontList($filters);

        // Use Trait method to get filter sidebar data
        $filterData = $this->getFilterSidebarData($request);

        $data = [
            'products'       => $products,
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'per_page_items' => CategoryRepo::getInstance()->getPerPageItems(),
        ];

        // Merge filter data
        $data = array_merge($data, $filterData);

        return nice_view('products.index', $data);
    }

    /**
     * @param  Request  $request
     * @param  Product  $product
     * @return mixed
     */
    public function show(Request $request, Product $product): mixed
    {
        $skuId = $request->get('sku_id');

        return $this->renderShow($product, $skuId);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug    = $request->slug;
        $product = ProductRepo::getInstance()->withActive()->builder(['slug' => $slug])->firstOrFail();

        $skuId = $request->get('sku_id');

        return $this->renderShow($product, $skuId);
    }

    /**
     * @param  Product  $product
     * @param  $skuId
     * @return mixed
     */
    private function renderShow(Product $product, $skuId): mixed
    {
        if (! $product->active) {
            abort(404);
        }

        if ($skuId) {
            $sku = Product\Sku::query()->find($skuId);
        }

        if (empty($sku)) {
            $sku = $product->masterSku;
        }

        $product->increment('viewed');
        $reviews     = ReviewRepo::getInstance()->getListByProduct($product, 10);
        $reviewStats = \NiceShoply\Common\Services\Review\ReviewService::getInstance()->getProductStats($product->id);
        $customerID  = current_customer_id();

        $rawVariables = $product->variables;
        if (! is_array($rawVariables)) {
            $rawVariables = [];
        }
        $variables = count($rawVariables) > 0
            ? ProductVariable::collection($rawVariables)->jsonSerialize()
            : [];

        $product->load([
            'productOptions' => function ($query) {
                $query->join('options', 'product_options.option_id', '=', 'options.id')
                    ->orderBy('options.position');
            },
            'productOptionValues' => function ($query) {
                $query->join('option_values', 'product_option_values.option_value_id', '=', 'option_values.id')
                    ->orderBy('option_values.position');
            },
        ]);
        $productOptions      = $product->productOptions;
        $productOptionValues = $product->productOptionValues;

        $data = [
            'product'             => $product,
            'sku'                 => $sku ? (new SkuListItem($sku))->jsonSerialize() : null,
            'skus'                => $product->skus->isNotEmpty() ? SkuListItem::collection($product->skus)->jsonSerialize() : [],
            'variants'            => $variables,
            'attributes'          => $product->groupedAttributes(),
            'reviews'             => $reviews,
            'reviewStats'         => $reviewStats,
            'reviewed'            => ReviewRepo::productReviewed($customerID, $product->id),
            'related'             => $product->relationProducts,
            'bundle_items'        => ProductRepo::getInstance()->getBundleItems($product),
            'productOptions'      => $productOptions,
            'productOptionValues' => $productOptionValues,
        ];

        return nice_view('products.show', $data);
    }

    /**
     * @param  Request  $request
     * @param  Product  $product
     * @return mixed
     */
    public function reviews(Request $request, Product $product): mixed
    {
        $page    = (int) $request->get('page', 1);
        $filters = [
            'rating'     => $request->get('rating'),
            'has_images' => $request->boolean('has_images'),
            'sort'       => $request->get('sort', 'latest'),
        ];

        $reviews = ReviewRepo::getInstance()->getListByProduct($product, 10, $page, array_filter($filters, fn ($v) => $v !== null && $v !== ''));

        $stats = \NiceShoply\Common\Services\Review\ReviewService::getInstance()->getProductStats($product->id);

        $html = view('products.components._review_list', [
            'reviews' => $reviews,
        ])->render();

        return response()->json([
            'success' => true,
            'data'    => [
                'html'     => $html,
                'has_more' => $reviews->hasMorePages(),
                'stats'    => $stats,
            ],
        ]);
    }
}
