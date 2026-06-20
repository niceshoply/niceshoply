<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'            => 'Visit Statistics',
    'detail_title'     => 'Visit Details',
    'statistics_title' => 'Visit Statistics',

    'device_desktop' => 'Desktop',
    'device_mobile'  => 'Mobile',
    'device_tablet'  => 'Tablet',

    'event_page_view'         => 'Page View',
    'event_product_view'      => 'Product View',
    'event_add_to_cart'       => 'Add to Cart',
    'event_checkout_start'    => 'Checkout Start',
    'event_order_placed'      => 'Order Placed',
    'event_payment_completed' => 'Payment Completed',
    'event_register'          => 'Register',
    'event_home_view'         => 'Home View',
    'event_category_view'     => 'Category View',
    'event_search'            => 'Search',
    'event_cart_view'         => 'Cart View',
    'event_order_cancelled'   => 'Order Cancelled',

    // List columns
    'customer'         => 'Customer',
    'guest'            => 'Guest',
    'ip_address'       => 'IP Address',
    'location'         => 'Location',
    'device'           => 'Device',
    'device_type'      => 'Device Type',
    'browser'          => 'Browser',
    'os'               => 'OS',
    'browser_os'       => 'Browser / OS',
    'referrer'         => 'Referrer',
    'country'          => 'Country',
    'customer_email'   => 'Customer Email',
    'first_visited_at' => 'First Visit',
    'last_visited_at'  => 'Last Visit',
    'visited_at'       => 'Visited At',

    // Enrich / aggregate
    'enrich'            => 'Enrich Data',
    'enrich_success'    => 'Enriched :count visit records',
    'enrich_done'       => 'Data enrichment completed',
    'enrich_failed'     => 'Data enrichment failed',
    'aggregate'         => 'Aggregate',
    'aggregate_success' => 'Aggregation completed',
    'aggregate_done'    => 'Aggregation completed',
    'aggregate_failed'  => 'Aggregation failed',
    'geo_unavailable'   => 'GeoIP database is not ready, location will be empty. Deploy the MaxMind GeoLite2 database before enriching data.',

    // Statistics page
    'period'              => 'Period',
    'period_day'          => 'Daily',
    'period_week'         => 'Weekly',
    'period_month'        => 'Monthly',
    'period_year'         => 'Yearly',
    'date'                => 'Date',
    'visit_metrics'       => 'Visit Metrics',
    'pv'                  => 'Page Views (PV)',
    'uv'                  => 'Unique Visitors (UV)',
    'ip'                  => 'Unique IPs',
    'new_visitors'        => 'New Visitors',
    'bounces'             => 'Bounces',
    'avg_duration'        => 'Avg. Duration',
    'device_distribution' => 'Device Distribution',
    'conversion_funnel'   => 'Conversion Funnel',
    'product_views'       => 'Product Views',
    'add_to_carts'        => 'Add to Cart',
    'checkout_starts'     => 'Checkout Start',
    'order_placed'        => 'Order Placed',
    'payment_completed'   => 'Payment Completed',
    'conversion_rates'    => 'Conversion Rates',
    'cart_to_checkout'    => 'Cart → Checkout',
    'checkout_to_order'   => 'Checkout → Order',
    'order_to_payment'    => 'Order → Payment',
    'overall_conversion'  => 'Overall Conversion',
];
