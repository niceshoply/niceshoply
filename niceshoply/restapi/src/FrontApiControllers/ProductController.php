<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\Product\FilterRepo;
use NiceShoply\Common\Repositories\Product\SkuRepo;
use NiceShoply\Common\Repositories\ProductRepo;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Resources\ProductDetail;
use NiceShoply\Common\Resources\ProductSimple;
use NiceShoply\Common\Resources\ReviewListItem;
use NiceShoply\Common\Resources\SkuSimple;

class ProductController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $sort  = $filters['sort'] ?? 'id';
        $order = $filters['order'] ?? 'desc';

        $builder = ProductRepo::getInstance()->withActive()->builder($filters);

        $productBuilder = clone $builder;

        if ($sort == 'price') {
            $productBuilder->select(['products.*', 'ps.price']);
            $productBuilder->join('product_skus as ps', function ($query) {
                $query->on('ps.product_id', '=', 'products.id')
                    ->where('is_default', true);
            });
            $productBuilder->orderBy('ps.price', $order);
        } elseif (in_array($sort, ['id', 'sales', 'viewed'])) {
            $productBuilder->orderBy($sort, $order);
        }

        $products = $productBuilder->paginate($perPage);

        return ProductSimple::collection($products)->additional([
            'filters' => FilterRepo::getInstance($builder)->getCurrentFilters(),
        ]);
    }

    /**
     * @param  Product  $product
     * @return mixed
     */
    public function show(Product $product): mixed
    {
        $single = new ProductDetail($product);

        return read_json_success($single);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function filters(Request $request): mixed
    {
        $builder = ProductRepo::getInstance()->builder($request->all());

        return FilterRepo::getInstance($builder)->getCurrentFilters();
    }

    /**
     * @param  Product  $product
     * @return AnonymousResourceCollection
     */
    public function productReviews(Request $request, Product $product): AnonymousResourceCollection
    {
        $filters = array_filter([
            'product_id' => $product->id,
            'status'     => Review::STATUS_APPROVED,
            'active'     => true,
            'rating'     => $request->get('rating'),
            'has_images' => $request->boolean('has_images') ?: null,
            'sort'       => $request->get('sort', 'latest'),
        ], fn ($v) => $v !== null && $v !== '');

        $list = ReviewRepo::getInstance()->builder($filters)->paginate();

        return ReviewListItem::collection($list);
    }

    /**
     * Summary of searchSku
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function skuList(Request $request)
    {
        $keyword = $request->get('keyword');
        $limit   = $request->get('limit', 10);
        $skus    = SkuRepo::getInstance()->searchByKeyword($keyword, $limit);

        return read_json_success(SkuSimple::collection($skus));
    }
}
