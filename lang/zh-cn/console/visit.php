<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'            => '访问统计',
    'detail_title'     => '访问明细',
    'statistics_title' => '访问统计',

    'device_desktop' => '桌面端',
    'device_mobile'  => '移动端',
    'device_tablet'  => '平板端',

    'event_page_view'         => '页面浏览',
    'event_product_view'      => '商品浏览',
    'event_add_to_cart'       => '加入购物车',
    'event_checkout_start'    => '开始结账',
    'event_order_placed'      => '已下单',
    'event_payment_completed' => '已支付',
    'event_register'          => '注册',
    'event_home_view'         => '首页浏览',
    'event_category_view'     => '分类浏览',
    'event_search'            => '搜索',
    'event_cart_view'         => '购物车浏览',
    'event_order_cancelled'   => '订单取消',

    // 列表列
    'customer'         => '客户',
    'guest'            => '游客',
    'ip_address'       => 'IP 地址',
    'location'         => '地理位置',
    'device'           => '设备',
    'device_type'      => '设备类型',
    'browser'          => '浏览器',
    'os'               => '操作系统',
    'browser_os'       => '浏览器 / 系统',
    'referrer'         => '来源',
    'country'          => '国家',
    'customer_email'   => '客户邮箱',
    'first_visited_at' => '首次访问',
    'last_visited_at'  => '最近访问',
    'visited_at'       => '访问时间',

    // 数据补全 / 聚合
    'enrich'            => '补全数据',
    'enrich_success'    => '已补全 :count 条访问记录',
    'enrich_done'       => '数据补全完成',
    'enrich_failed'     => '数据补全失败',
    'aggregate'         => '聚合统计',
    'aggregate_success' => '聚合完成',
    'aggregate_done'    => '聚合完成',
    'aggregate_failed'  => '聚合失败',
    'geo_unavailable'   => 'GeoIP 数据库未就绪，地理位置将为空。请部署 MaxMind GeoLite2 数据库后再补全数据。',

    // 统计页
    'period'              => '周期',
    'period_day'          => '按日',
    'period_week'         => '按周',
    'period_month'        => '按月',
    'period_year'         => '按年',
    'date'                => '日期',
    'visit_metrics'       => '访问指标',
    'pv'                  => '浏览量 (PV)',
    'uv'                  => '访客数 (UV)',
    'ip'                  => '独立 IP',
    'new_visitors'        => '新访客',
    'bounces'             => '跳出数',
    'avg_duration'        => '平均时长',
    'device_distribution' => '设备分布',
    'conversion_funnel'   => '转化漏斗',
    'product_views'       => '商品浏览',
    'add_to_carts'        => '加入购物车',
    'checkout_starts'     => '开始结账',
    'order_placed'        => '提交订单',
    'payment_completed'   => '完成支付',
    'conversion_rates'    => '转化率',
    'cart_to_checkout'    => '加购 → 结账',
    'checkout_to_order'   => '结账 → 下单',
    'order_to_payment'    => '下单 → 支付',
    'overall_conversion'  => '整体转化率',
];
