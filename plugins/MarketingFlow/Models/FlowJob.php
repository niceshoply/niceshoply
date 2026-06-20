<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MarketingFlow\Models;

use Illuminate\Database\Eloquent\Model;

class FlowJob extends Model
{
    protected $table = 'marketing_flow_jobs';

    protected $guarded = [];

    protected $casts = [
        'run_at' => 'datetime',
    ];
}
