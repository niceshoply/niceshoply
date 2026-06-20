<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GiftCard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCardBatch extends Model
{
    protected $table = 'gift_card_batches';

    protected $guarded = [];

    protected $casts = [
        'face_value' => 'float',
        'expire_at'  => 'date',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(GiftCard::class, 'batch_id', 'id');
    }
}
