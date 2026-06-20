<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                 => 'Restock/Price Alerts',
    'enabled'              => 'Enable Restock/Price Alerts',
    'yes'                  => 'Yes',
    'no'                   => 'No',

    'need_login'           => 'Please sign in first',
    'subscribed'           => 'Alert subscribed',
    'cancelled'            => 'Alert cancelled',

    'restock_title'        => 'Your wished item is back in stock',
    'restock_content'      => 'Item (SKU :sku) is back in stock. First come, first served!',
    'price_drop_title'     => 'Price dropped on your wished item',
    'price_drop_content'   => 'Item (SKU :sku) has dropped to :price. Order now!',

    'pending_count'        => 'Pending alerts',
    'scan_now'             => 'Scan & send now',
    'scan_done'            => 'Sent :count alert(s)',
    'scanning'             => 'Scanning…',
    'cron_hint'            => 'Add to server cron: php artisan stock:notify (e.g. every 30 min).',

    'customer_id'          => 'Customer ID',
    'sku_code'             => 'SKU Code',
    'type'                 => 'Type',
    'type_restock'         => 'Restock',
    'type_price_drop'      => 'Price Drop',
    'target_price'         => 'Target Price',
    'status'               => 'Status',
    'st_pending'           => 'Pending',
    'st_notified'          => 'Notified',
    'st_cancelled'         => 'Cancelled',
    'no_data'              => 'No subscriptions yet',
];
