<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'status_pending'    => 'Pending',
    'status_processing' => 'Processing',
    'status_succeeded'  => 'Succeeded',
    'status_failed'     => 'Failed',
    'status_cancelled'  => 'Cancelled',

    'amount_invalid'        => 'Refund amount must be greater than 0',
    'amount_exceeds_order'  => 'Refund amount exceeds the refundable order total',
    'method_invalid'        => 'Invalid refund method',
    'balance_need_customer' => 'Refunding to wallet requires a linked customer',
    'balance_comment'       => 'Refund :number to wallet balance',
    'gateway_unsupported'   => 'Gateway :gateway does not support original refunds; use manual or balance instead',
    'invalid_transition'    => 'Refund cannot transition from :from to :to',
];
