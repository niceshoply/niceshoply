<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                 => '购物车挽回',
    'enabled'              => '启用购物车挽回',
    'yes'                  => '是',
    'no'                   => '否',

    'idle_hours'           => '弃购判定（闲置小时数）',
    'cooldown_days'        => '召回最小间隔（天）',
    'recovery_coupon_code' => '召回券码（展示在消息中）',

    'recover_title'        => '你的购物车还在等你～',
    'recover_content'      => '你的购物车里还有 :count 件商品尚未结算，趁现在下单吧！',
    'coupon_line'          => '专属召回券码：:code',

    'total_sent'           => '累计召回次数',
    'scan_now'             => '立即扫描并发送',
    'scan_done'            => '本次召回 :count 位会员',
    'scanning'             => '扫描中…',
    'cron_hint'            => '建议在服务器 cron 中加入：php artisan cart:recover（如每小时一次），实现自动召回。',

    'customer_id'          => '会员ID',
    'item_count'           => '购物车件数',
    'channel'              => '渠道',
    'sent_at'              => '发送时间',
    'no_data'              => '暂无召回记录',
];
