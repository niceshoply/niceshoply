<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'status_pending'    => '待处理',
    'status_processing' => '处理中',
    'status_succeeded'  => '已成功',
    'status_failed'     => '已失败',
    'status_cancelled'  => '已取消',

    'amount_invalid'        => '退款金额必须大于 0',
    'amount_exceeds_order'  => '退款金额超过订单可退总额',
    'method_invalid'        => '无效的退款方式',
    'balance_need_customer' => '退回余额需要绑定客户',
    'balance_comment'       => '退款单 :number 退回余额',
    'gateway_unsupported'   => '支付网关 :gateway 不支持原路退款，请改用人工或余额退款',
    'invalid_transition'    => '退款单状态不能从 :from 流转到 :to',
];
