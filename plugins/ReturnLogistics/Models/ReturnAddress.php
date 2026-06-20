<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReturnLogistics\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnAddress extends Model
{
    protected $table = 'return_addresses';

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function fullAddress(): string
    {
        return trim(($this->province ?? '').($this->city ?? '').($this->area ?? '').($this->address ?? ''));
    }
}
