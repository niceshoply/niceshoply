<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NiceShoply\Common\Models\Order;

/**
 * 订单状态发生流转时触发。
 *
 * 与既有 Hook（fire_hook_action）互补：Hook 面向插件扩展点，
 * 本事件面向应用内的异步监听（队列化通知、统计、Webhook 推送等）。
 */
class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $fromStatus,
        public string $toStatus,
    ) {}
}
