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

class VirtualGood extends Model
{
    protected $table = 'virtual_goods';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPE_CARD = 'card';
    public const TYPE_TEXT = 'text';
}
