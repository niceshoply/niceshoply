<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Customer;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Models\Customer;

class Social extends BaseModel
{
    protected $table = 'customer_socials';

    protected $fillable = [
        'customer_id', 'provider', 'user_id', 'union_id', 'access_token', 'refresh_token', 'reference',
    ];

    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
