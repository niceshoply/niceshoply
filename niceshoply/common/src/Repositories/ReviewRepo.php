<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Services\Review\ReviewService;
use Throwable;

class ReviewRepo extends BaseRepo
{
    protected string $model = Review::class;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'product', 'type' => 'input', 'label' => trans('console/review.product')],
            ['name' => 'rating', 'type' => 'input', 'label' => trans('console/review.rating')],
            ['name' => 'status', 'type' => 'select', 'label' => trans('console/review.status'), 'options' => self::getStatusOptions(), 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'review_content', 'type' => 'input', 'label' => trans('console/review.review_content')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('console/review.created_at')],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getStatusOptions(): array
    {
        return [
            ['code' => Review::STATUS_PENDING, 'label' => trans('console/review.status_pending')],
            ['code' => Review::STATUS_APPROVED, 'label' => trans('console/review.status_approved')],
            ['code' => Review::STATUS_REJECTED, 'label' => trans('console/review.status_rejected')],
        ];
    }

    public static function productReviewed($customerID, $productID): bool
    {
        if (empty($customerID) || empty($productID)) {
            return false;
        }

        return Review::query()
            ->where('customer_id', $customerID)
            ->where('product_id', $productID)
            ->exists();
    }

    public static function orderReviewed($customerID, $orderItemID): bool
    {
        if (empty($customerID) || empty($orderItemID)) {
            return false;
        }

        return Review::query()
            ->where('customer_id', $customerID)
            ->where('order_item_id', $orderItemID)
            ->exists();
    }

    /**
     * 前台商品评价列表（仅已通过审核）。
     *
     * @param  array<string, mixed>  $extraFilters
     */
    public function getListByProduct($product, int $limit = 10, int $page = 1, array $extraFilters = []): LengthAwarePaginator
    {
        $productID = is_object($product) ? $product->id : (int) $product;

        $filters = array_merge([
            'product_id' => $productID,
            'status'     => Review::STATUS_APPROVED,
            'active'     => true,
        ], $extraFilters);

        return $this->builder($filters)->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  bool  $fromAdmin
     *
     * @throws Throwable
     */
    public function create($data, bool $fromAdmin = false): mixed
    {
        $data   = ReviewService::getInstance()->prepareCreateData($data, $fromAdmin);
        $review = null;

        if ($data['customer_id'] && $data['order_item_id']) {
            $review = $this->builder([
                'customer_id'   => $data['customer_id'],
                'order_item_id' => $data['order_item_id'],
            ])->first();
        }

        if ($review) {
            return $review;
        }

        if ($data['order_item_id'] && empty($data['product_id'])) {
            $orderItem          = Item::query()->find($data['order_item_id']);
            $data['product_id'] = $orderItem->product_id ?? 0;
        }

        $review = new Review($data);
        $review->saveOrFail();

        return $review;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Review::query()->with([
            'customer',
            'product',
            'orderItem',
        ]);

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $productID = $filters['product_id'] ?? 0;
        if ($productID) {
            $builder->where('product_id', $productID);
        }

        $orderItemID = $filters['order_item_id'] ?? 0;
        if ($orderItemID) {
            $builder->where('order_item_id', $orderItemID);
        }

        $content = $filters['content'] ?? ($filters['review_content'] ?? '');
        if ($content) {
            $builder->where('content', 'like', "%$content%");
        }

        $rating = $filters['rating'] ?? '';
        if ($rating !== '') {
            $builder->where('rating', (int) $rating);
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        if (! empty($filters['has_images'])) {
            $builder->whereNotNull('images')->where('images', '!=', '[]');
        }

        $product = $filters['product'] ?? '';
        if ($product) {
            $builder->whereHas('product.translation', function (Builder $query) use ($product) {
                $query->where('name', 'like', "%$product%");
            });
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        $sort = $filters['sort'] ?? 'latest';
        if ($sort === 'rating_desc') {
            $builder->orderByDesc('rating')->orderByDesc('id');
        } else {
            $builder->orderByDesc('id');
        }

        return $builder;
    }
}
