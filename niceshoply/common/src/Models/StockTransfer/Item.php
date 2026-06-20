<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\StockTransfer;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\StockTransfer;

class Item extends BaseModel
{
    protected $table = 'stock_transfer_items';

    protected $fillable = [
        'stock_transfer_id', 'sku_code', 'quantity', 'received_quantity',
    ];

    /**
     * @return BelongsTo
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }
}
