<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Models;

use Illuminate\Database\Eloquent\Model;

class BargainTask extends Model
{
    protected $table = 'bargain_tasks';

    protected $guarded = [];

    protected $casts = [
        'origin_price'  => 'float',
        'floor_price'   => 'float',
        'current_price' => 'float',
        'expire_at'     => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(BargainActivity::class, 'activity_id');
    }

    public function records()
    {
        return $this->hasMany(BargainRecord::class, 'task_id');
    }
}
