<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                 => '周期购/订阅',
    'default_payment_mode' => '默认付款方式',
    'mode_reminder'        => '到期提醒（手动支付）',
    'mode_auto_balance'    => '余额自动扣款',

    'need_login'           => '请先登录',
    'sku_not_found'        => '商品规格不存在',
    'invalid_status'       => '状态不合法',
    'subscribed'           => '订阅成功',
    'paused'               => '已暂停',
    'resumed'              => '已恢复',
    'cancelled'            => '已取消',

    'billing_name'         => '周期购',
    'order_comment'        => '周期购订单：:name',
    'tx_comment'           => '周期购扣款（订单 :number）',

    'notify_title'         => '周期购订单提醒',
    'notify_paid'          => '您的周期购「:name」已自动下单并用余额支付（订单 :number）。',
    'notify_unpaid'        => '您的周期购「:name」已生成订单 :number，请尽快前往支付。',

    'run_done'             => '执行完成：处理 :processed 个，自动支付 :paid 个，失败 :failed 个',

    // console
    'stat_active'          => '进行中',
    'stat_paused'          => '已暂停',
    'stat_cancelled'       => '已取消',
    'stat_due'             => '待执行',
    'run_now'              => '立即执行到期订阅',
    'no_data'              => '暂无订阅',
    'id'                   => '#',
    'customer'             => '会员ID',
    'product'              => '商品',
    'sku'                  => '规格',
    'qty'                  => '数量',
    'interval'             => '周期',
    'payment_mode'         => '付款方式',
    'status'               => '状态',
    'next_run'             => '下次执行',
    'cycles'               => '已执行期数',
    'unit_day'             => '天',
    'unit_week'            => '周',
    'unit_month'           => '月',
    'every'                => '每',
];
