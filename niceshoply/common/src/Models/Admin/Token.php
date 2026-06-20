<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\Admin;
use NiceShoply\Common\Models\BaseModel;

class Token extends BaseModel
{
    protected $table = 'admin_tokens';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
