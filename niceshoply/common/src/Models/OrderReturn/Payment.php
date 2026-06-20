<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\OrderReturn;

use NiceShoply\Common\Models\BaseModel;

class Payment extends BaseModel
{
    protected $table = 'order_return_payments';

    protected $fillable = [
        'order_return_id', 'amount', 'type', 'status', 'comment',
    ];

    /**
     * Refund back to the customer wallet balance.
     */
    public const TYPE_WALLET = 'wallet';

    /**
     * Refund back through the original payment channel (offline / manual).
     */
    public const TYPE_ORIGINAL = 'original';

    /**
     * Refund completed.
     */
    public const STATUS_COMPLETED = 'completed';
}
