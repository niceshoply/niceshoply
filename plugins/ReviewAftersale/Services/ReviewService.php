<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Services;

use Plugin\ReviewAftersale\Models\ProductReview;

class ReviewService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 提交评价。是否需要审核取决于配置。
     */
    public function submit(int $customerId, int $productId, int $rating, string $content = '', array $images = [], int $orderId = 0): ProductReview
    {
        $needAudit = (bool) plugin_setting('review_aftersale', 'review_need_audit', true);

        return ProductReview::query()->create([
            'product_id'  => $productId,
            'customer_id' => $customerId,
            'order_id'    => $orderId,
            'rating'      => max(1, min(5, $rating)),
            'content'     => $content,
            'images'      => array_values($images),
            'status'      => $needAudit ? 'pending' : 'approved',
        ]);
    }

    /**
     * 商品已审核评价分页。
     */
    public function listForProduct(int $productId, int $perPage = 10)
    {
        return ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 商品评分概要。
     *
     * @return array{count:int, average:float}
     */
    public function summary(int $productId): array
    {
        $query = ProductReview::query()->where('product_id', $productId)->where('status', 'approved');

        return [
            'count'   => (int) $query->count(),
            'average' => round((float) $query->avg('rating'), 1),
        ];
    }
}
