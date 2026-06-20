<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\VirtualGoods\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\VirtualGoods\Models\VirtualDelivery;

class VirtualGoodsController extends BaseController
{
    /**
     * 当前会员的虚拟商品发放记录（卡密/内容）。
     */
    public function index(): mixed
    {
        $customerId = (int) token_customer_id();
        if ($customerId <= 0) {
            return json_fail(__('VirtualGoods::common.need_login'));
        }

        $deliveries = VirtualDelivery::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->get(['id', 'order_id', 'product_sku', 'content', 'created_at']);

        return json_success('ok', $deliveries);
    }
}
