<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Warehouse;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Warehouse;

class Stock extends BaseModel
{
    protected $table = 'warehouse_stocks';

    protected $fillable = [
        'warehouse_id', 'product_id', 'sku_id', 'sku_code',
        'quantity', 'reserved_quantity', 'low_stock_threshold',
    ];

    /**
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * @return BelongsTo
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class, 'sku_id');
    }

    /**
     * @return int
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }
}
