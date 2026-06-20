<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Order;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;

class ShipmentItem extends BaseModel
{
    protected $table = 'order_shipment_items';

    protected $fillable = [
        'shipment_id', 'order_item_id', 'sku_code', 'quantity',
    ];

    /**
     * @return BelongsTo
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    /**
     * @return BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'order_item_id');
    }
}
