<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NewCustomer\Services;

use NiceShoply\Common\Models\Order;
use Plugin\NotifyCenter\Services\NotifyService;

class NewCustomerService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 是否为新客（无历史订单）。结算阶段当前单未创建，count==0 即首单。
     */
    public function isNewCustomer(int $customerId): bool
    {
        if ($customerId <= 0) {
            return false;
        }

        return ! Order::query()->where('customer_id', $customerId)->exists();
    }

    /**
     * 计算首单折扣金额（正数）。
     */
    public function computeDiscount(float $subtotal): float
    {
        $minAmount = (float) plugin_setting('new_customer', 'min_amount', 0);
        if ($minAmount > 0 && $subtotal < $minAmount) {
            return 0;
        }

        $type  = (string) plugin_setting('new_customer', 'discount_type', 'fixed');
        $value = (float) plugin_setting('new_customer', 'discount_value', 0);
        if ($value <= 0) {
            return 0;
        }

        if ($type === 'percent') {
            $discount    = $subtotal * $value / 100;
            $maxDiscount = (float) plugin_setting('new_customer', 'max_discount', 0);
            if ($maxDiscount > 0) {
                $discount = min($discount, $maxDiscount);
            }
        } else {
            $discount = min($value, $subtotal);
        }

        return round(max($discount, 0), 2);
    }

    /**
     * 注册后发送欢迎站内信（NotifyCenter 未安装时静默跳过）。
     */
    public function welcome($customer): void
    {
        if (! $customer) {
            return;
        }
        $customerId = (int) ($customer->id ?? 0);
        if ($customerId <= 0 || ! class_exists(NotifyService::class)) {
            return;
        }

        $message = trim((string) plugin_setting('new_customer', 'welcome_message', ''));
        if ($message === '') {
            $message = __('NewCustomer::common.default_welcome');
        }

        $code = trim((string) plugin_setting('new_customer', 'welcome_coupon_code', ''));
        if ($code !== '') {
            $message .= "\n".__('NewCustomer::common.coupon_line', ['code' => $code]);
        }

        NotifyService::getInstance()->notify(
            $customerId,
            __('NewCustomer::common.welcome_title'),
            $message,
            'system'
        );
    }
}
