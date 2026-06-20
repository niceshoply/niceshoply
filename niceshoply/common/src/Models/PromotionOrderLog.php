<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

/**
 * 订单促销应用流水模型
 */
class PromotionOrderLog extends BaseModel
{
    protected $table = 'nice_promotion_order_logs';

    protected $fillable = [
        'order_id',
        'promotion_id',
        'coupon_id',
        'code',
        'discount_amount',
        'snapshot',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:4',
        'snapshot'        => 'array',
    ];
}
