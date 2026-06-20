<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\VirtualGoods\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualCard extends Model
{
    protected $table = 'virtual_cards';

    protected $guarded = [];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public const STATUS_UNUSED = 'unused';
    public const STATUS_USED   = 'used';
}
