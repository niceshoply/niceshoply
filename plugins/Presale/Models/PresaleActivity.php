<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresaleActivity extends Model
{
    protected $table = 'presale_activities';

    protected $guarded = [];

    protected $casts = [
        'active'    => 'boolean',
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
        'ship_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PresaleItem::class, 'presale_id', 'id');
    }
}
