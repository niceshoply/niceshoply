<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                 => 'Cart Recovery',
    'enabled'              => 'Enable Cart Recovery',
    'yes'                  => 'Yes',
    'no'                   => 'No',

    'idle_hours'           => 'Abandon threshold (idle hours)',
    'cooldown_days'        => 'Min interval between reminders (days)',
    'recovery_coupon_code' => 'Recovery coupon code (shown in message)',

    'recover_title'        => 'Your cart is waiting for you!',
    'recover_content'      => 'You still have :count item(s) in your cart. Complete your order now!',
    'coupon_line'          => 'Your recovery coupon code: :code',

    'total_sent'           => 'Total reminders sent',
    'scan_now'             => 'Scan & send now',
    'scan_done'            => 'Recovered :count member(s)',
    'scanning'             => 'Scanning…',
    'cron_hint'            => 'Add to server cron: php artisan cart:recover (e.g. hourly) to automate recovery.',

    'customer_id'          => 'Customer ID',
    'item_count'           => 'Cart items',
    'channel'              => 'Channel',
    'sent_at'              => 'Sent at',
    'no_data'              => 'No recovery records yet',
];
