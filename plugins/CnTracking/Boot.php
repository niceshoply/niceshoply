<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnTracking;

class Boot
{
    public function init(): void
    {
        // 提供物流轨迹查询过滤器，供主题/订单页调用：
        // fire_hook_filter('service.tracking.query', ['company' => 'SF', 'number' => '...'])
        listen_hook_filter('service.tracking.query', function (array $payload) {
            try {
                $result = Services\TrackingService::getInstance()->query(
                    (string) ($payload['company'] ?? ''),
                    (string) ($payload['number'] ?? ''),
                    (string) ($payload['phone'] ?? '')
                );
                $payload['result'] = $result;
            } catch (\Throwable $e) {
                $payload['error'] = $e->getMessage();
            }

            return $payload;
        });
    }
}
