<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\VirtualGoods;

use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\VirtualGoods\Services\VirtualGoodsService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 订单支付成功后自动发放虚拟商品
        listen_hook_action('service.state_machine.change_status.after', function (array $data) {
            $status = (string) ($data['status'] ?? '');
            if ($status !== StateMachineService::PAID) {
                return;
            }
            VirtualGoodsService::getInstance()->handleOrderPaid($data['order'] ?? null);
        });

        listen_hook_filter('console.component.sidebar.product.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'virtual_goods.index',
                'title'           => __('VirtualGoods::common.menu'),
                'url'             => console_route('virtual_goods.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
