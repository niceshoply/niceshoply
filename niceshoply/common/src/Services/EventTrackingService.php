<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use NiceShoply\Common\Models\Visit\VisitEvent;

/**
 * 事件追踪服务
 *
 * 记录转化漏斗各类事件（商品浏览、加购、结账、下单、支付、注册等）。
 */
class EventTrackingService
{
    /**
     * 追踪一个事件。
     *
     * @param  string  $eventType
     * @param  array  $eventData
     * @param  Request|null  $request
     * @param  int|null  $customerId
     * @param  string|null  $source  'web' 或 'api'
     * @return VisitEvent|null
     */
    public function track(string $eventType, array $eventData = [], ?Request $request = null, ?int $customerId = null, ?string $source = null): ?VisitEvent
    {
        try {
            $request = $request ?? request();

            if ($source === null && $request) {
                $source = $this->detectSource($request);
            }

            $source = $source ?? 'web';

            $eventData['source'] = $source;

            $sessionId = Session::getId();

            if (empty($sessionId)) {
                return null;
            }

            if ($customerId === null && $request) {
                $customerId = current_customer()?->id;
            }

            $ipAddress = $request ? $this->getClientIp($request) : null;

            return VisitEvent::create([
                'session_id'  => $sessionId,
                'event_type'  => $eventType,
                'event_data'  => $eventData,
                'customer_id' => $customerId,
                'ip_address'  => $ipAddress,
                'page_url'    => $request ? $request->fullUrl() : null,
                'referrer'    => $request ? $request->header('referer') : null,
            ]);
        } catch (Exception $e) {
            Log::error('EventTrackingService: 追踪事件失败', [
                'event_type' => $eventType,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 判定请求来源（web 或 api）。
     *
     * @param  Request  $request
     * @return string
     */
    private function detectSource(Request $request): string
    {
        $path = $request->path();

        if (str_starts_with($path, 'api/') || $request->is('api/*')) {
            return 'api';
        }

        return 'web';
    }

    /**
     * 追踪页面浏览事件。
     */
    public function trackPageView(?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_PAGE_VIEW, [
            'url' => $request ? $request->fullUrl() : null,
        ], $request);
    }

    /**
     * 追踪商品浏览事件。
     */
    public function trackProductView(int $productId, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_PRODUCT_VIEW, ['product_id' => $productId], $request);
    }

    /**
     * 追踪加入购物车事件。
     */
    public function trackAddToCart(int $productId, int $quantity = 1, ?float $price = null, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_ADD_TO_CART, [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'price'      => $price,
        ], $request);
    }

    /**
     * 追踪开始结账事件。
     */
    public function trackCheckoutStart(?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_CHECKOUT_START, [], $request);
    }

    /**
     * 追踪下单事件。
     */
    public function trackOrderPlaced(int $orderId, string $orderNumber, float $total, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_ORDER_PLACED, [
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'total'        => $total,
        ], $request);
    }

    /**
     * 追踪支付完成事件。
     */
    public function trackPaymentCompleted(int $orderId, string $orderNumber, float $amount, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_PAYMENT_COMPLETED, [
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'amount'       => $amount,
        ], $request);
    }

    /**
     * 追踪注册事件。
     */
    public function trackRegister(int $customerId, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_REGISTER, ['customer_id' => $customerId], $request, $customerId);
    }

    /**
     * 追踪首页浏览事件。
     */
    public function trackHomeView(?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_HOME_VIEW, [], $request);
    }

    /**
     * 追踪分类浏览事件。
     */
    public function trackCategoryView(int $categoryId, string $categoryName, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_CATEGORY_VIEW, [
            'category_id'   => $categoryId,
            'category_name' => $categoryName,
        ], $request);
    }

    /**
     * 追踪搜索事件。
     */
    public function trackSearch(string $keyword, int $resultCount, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_SEARCH, [
            'keyword'      => $keyword,
            'result_count' => $resultCount,
        ], $request);
    }

    /**
     * 追踪购物车浏览事件。
     */
    public function trackCartView(int $itemCount, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_CART_VIEW, ['item_count' => $itemCount], $request);
    }

    /**
     * 追踪订单取消事件。
     */
    public function trackOrderCancelled(int $orderId, string $orderNumber, string $reason = '', ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_ORDER_CANCELLED, [
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'reason'       => $reason,
        ], $request);
    }

    /**
     * 获取客户端 IP。
     *
     * @param  Request  $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        $ip = $request->ip();

        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        return $ip;
    }
}
