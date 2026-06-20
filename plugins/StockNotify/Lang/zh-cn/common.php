<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                 => '到货/降价提醒',
    'enabled'              => '启用到货/降价提醒',
    'yes'                  => '是',
    'no'                   => '否',

    'need_login'           => '请先登录',
    'subscribed'           => '提醒登记成功',
    'cancelled'            => '已取消提醒',

    'restock_title'        => '你关注的商品已到货',
    'restock_content'      => '商品(SKU::sku)已重新到货，先到先得！',
    'price_drop_title'     => '你关注的商品降价啦',
    'price_drop_content'   => '商品(SKU::sku)已降到 :price，快来下单！',

    'pending_count'        => '待提醒数量',
    'scan_now'             => '立即扫描并发送',
    'scan_done'            => '本次发送 :count 条提醒',
    'scanning'             => '扫描中…',
    'cron_hint'            => '建议在服务器 cron 中加入：php artisan stock:notify（如每 30 分钟一次）。',

    'customer_id'          => '会员ID',
    'sku_code'             => 'SKU 编码',
    'type'                 => '类型',
    'type_restock'         => '到货提醒',
    'type_price_drop'      => '降价提醒',
    'target_price'         => '目标价',
    'status'               => '状态',
    'st_pending'           => '待提醒',
    'st_notified'          => '已提醒',
    'st_cancelled'         => '已取消',
    'no_data'              => '暂无提醒登记',
];
