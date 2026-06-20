<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlashSale\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    protected $table = 'flash_sales';

    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'active'   => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(FlashSaleItem::class, 'flash_sale_id');
    }
}
