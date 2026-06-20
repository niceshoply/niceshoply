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
use NiceShoply\Common\Models\Warehouse;

class StockMovement extends BaseModel
{
    protected $table = 'warehouse_stock_movements';

    public const TYPE_INBOUND = 'inbound';

    public const TYPE_OUTBOUND = 'outbound';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_RESERVATION = 'reservation';

    public const TYPE_RELEASE = 'release';

    public const TYPES = [
        self::TYPE_INBOUND,
        self::TYPE_OUTBOUND,
        self::TYPE_TRANSFER_IN,
        self::TYPE_TRANSFER_OUT,
        self::TYPE_ADJUSTMENT,
        self::TYPE_RESERVATION,
        self::TYPE_RELEASE,
    ];

    protected $fillable = [
        'warehouse_id', 'sku_code', 'quantity', 'type',
        'reference_type', 'reference_id', 'note', 'admin_id',
    ];

    /**
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
