<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AnalyticsAds;

use Plugin\AnalyticsAds\Services\AnalyticsService;

class Boot
{
    public function init(): void
    {
        // 注入统计/像素代码到 <head>
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return AnalyticsService::getInstance()->renderHead();
        });

        // 支付/结账成功页触发购买转化事件
        listen_blade_insert('checkout.success.bottom', function ($data = []) {
            $order = is_array($data) ? ($data['order'] ?? null) : null;
            if (! $order) {
                return '';
            }

            $currency = function_exists('setting_currency_code') ? (string) setting_currency_code() : 'CNY';

            return AnalyticsService::getInstance()->renderPurchaseEvent(
                (float) ($order->total ?? 0),
                $currency,
                (string) ($order->number ?? '')
            );
        });
    }
}
