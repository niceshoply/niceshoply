<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Review;

use Exception;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Repositories\ReviewRepo;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\HtmlPurifyService;
use NiceShoply\Common\Services\StateMachineService;

/**
 * 评价领域服务：内容净化、已购校验、审核与统计。
 */
class ReviewService extends BaseService
{
    /**
     * 提交评价前校验并规范化数据。
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $fromAdmin  后台录入可跳过已购校验
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function prepareCreateData(array $data, bool $fromAdmin = false): array
    {
        $customerId  = (int) ($data['customer_id'] ?? 0);
        $orderItemId = (int) ($data['order_item_id'] ?? 0);
        $productId   = (int) ($data['product_id'] ?? 0);

        if (! $fromAdmin) {
            $this->assertCustomerCanReview($customerId, $orderItemId, $productId);
        }

        $content = $this->sanitizeContent((string) ($data['content'] ?? ''));
        if ($content === '') {
            throw new Exception(trans('front/review.content_required'));
        }

        $images = $this->normalizeImages($data['images'] ?? []);
        $status = $this->resolveInitialStatus($fromAdmin);

        return [
            'customer_id'       => $customerId,
            'product_id'        => $productId,
            'order_item_id'     => $orderItemId,
            'rating'            => max(1, min(5, (int) ($data['rating'] ?? 5))),
            'rating_dimensions' => $this->normalizeDimensions($data['rating_dimensions'] ?? []),
            'content'           => $content,
            'images'            => $images,
            'like'              => 0,
            'dislike'           => 0,
            'active'            => $status === Review::STATUS_APPROVED,
            'status'            => $status,
        ];
    }

    /**
     * 审核通过。
     */
    public function approve(Review $review): Review
    {
        $review->status = Review::STATUS_APPROVED;
        $review->active = true;
        $review->save();

        return $review;
    }

    /**
     * 审核拒绝。
     */
    public function reject(Review $review): Review
    {
        $review->status = Review::STATUS_REJECTED;
        $review->active = false;
        $review->save();

        return $review;
    }

    /**
     * 商家回复。
     */
    public function reply(Review $review, string $reply): Review
    {
        $review->reply    = $this->sanitizeContent($reply);
        $review->reply_at = Carbon::now();
        $review->save();

        return $review;
    }

    /**
     * 商品好评率统计（4-5 星占比）。
     *
     * @return array{total: int, positive: int, rate: float}
     */
    public function getProductStats(int $productId): array
    {
        $base = Review::query()
            ->where('product_id', $productId)
            ->where('status', Review::STATUS_APPROVED);

        $total    = (clone $base)->count();
        $positive = (clone $base)->where('rating', '>=', 4)->count();
        $rate     = $total > 0 ? round($positive / $total * 100, 1) : 0.0;

        return [
            'total'    => $total,
            'positive' => $positive,
            'rate'     => $rate,
        ];
    }

    /**
     * 全局待审数量。
     */
    public function pendingCount(): int
    {
        return Review::query()->where('status', Review::STATUS_PENDING)->count();
    }

    /**
     * 已购校验：必须关联 order_item 且订单已支付/完成。
     *
     * @throws Exception
     */
    public function assertCustomerCanReview(int $customerId, int $orderItemId, int $productId): void
    {
        if ($customerId <= 0) {
            throw new Exception(trans('front/review.login_required'));
        }

        $requirePurchase = (bool) system_setting('bought_review', false);

        if ($orderItemId <= 0) {
            if ($requirePurchase) {
                throw new Exception(trans('front/review.order_item_required'));
            }

            if ($productId <= 0) {
                throw new Exception(trans('front/review.invalid_product'));
            }

            return;
        }

        /** @var Item|null $orderItem */
        $orderItem = Item::query()->with('order')->find($orderItemId);
        if (! $orderItem) {
            throw new Exception(trans('front/review.invalid_order_item'));
        }

        $order = $orderItem->order;
        if (! $order || (int) $order->customer_id !== $customerId) {
            throw new Exception(trans('front/review.not_your_order'));
        }

        if (! in_array($order->status, $this->reviewableOrderStatuses(), true)) {
            throw new Exception(trans('front/review.order_not_reviewable'));
        }

        if ($productId > 0 && (int) $orderItem->product_id !== $productId) {
            throw new Exception(trans('front/review.product_mismatch'));
        }

        if (ReviewRepo::orderReviewed($customerId, $orderItemId)) {
            throw new Exception(trans('front/review.already_reviewed'));
        }
    }

    /**
     * 净化评价正文并过滤敏感词。
     */
    public function sanitizeContent(string $content): string
    {
        $text = HtmlPurifyService::strip($content);
        $text = $this->filterSensitiveWords($text);

        return trim($text);
    }

    /**
     * 敏感词过滤（系统设置 review_sensitive_words，每行一词）。
     */
    public function filterSensitiveWords(string $text): string
    {
        $raw = (string) system_setting('review_sensitive_words', '');
        if ($raw === '') {
            return $text;
        }

        foreach (preg_split('/\r\n|\r|\n/', $raw) as $word) {
            $word = trim($word);
            if ($word === '') {
                continue;
            }
            $text = str_ireplace($word, str_repeat('*', mb_strlen($word)), $text);
        }

        return $text;
    }

    /**
     * @param  mixed  $images
     * @return array<int, string>
     */
    public function normalizeImages(mixed $images): array
    {
        if (is_string($images)) {
            $images = preg_split('/\r\n|\r|\n|,/', $images) ?: [];
        }

        if (! is_array($images)) {
            return [];
        }

        $result = [];
        foreach ($images as $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            // 仅保留路径字符串，防止注入
            $result[] = HtmlPurifyService::strip($url);
        }

        return array_values(array_unique(array_slice($result, 0, 9)));
    }

    /**
     * @param  mixed  $dimensions
     * @return array<string, int>
     */
    private function normalizeDimensions(mixed $dimensions): array
    {
        if (is_string($dimensions)) {
            $decoded = json_decode($dimensions, true);

            return is_array($decoded) ? $this->normalizeDimensions($decoded) : [];
        }

        if (! is_array($dimensions)) {
            return [];
        }

        $result = [];
        foreach ($dimensions as $key => $value) {
            if (! is_string($key)) {
                continue;
            }
            $result[$key] = max(1, min(5, (int) $value));
        }

        return $result;
    }

    private function resolveInitialStatus(bool $fromAdmin): string
    {
        if ($fromAdmin) {
            return Review::STATUS_APPROVED;
        }

        return (bool) system_setting('review_audit', false)
            ? Review::STATUS_PENDING
            : Review::STATUS_APPROVED;
    }

    /**
     * @return array<int, string>
     */
    private function reviewableOrderStatuses(): array
    {
        return [
            StateMachineService::PAID,
            StateMachineService::PARTIALLY_SHIPPED,
            StateMachineService::SHIPPED,
            StateMachineService::COMPLETED,
        ];
    }
}
