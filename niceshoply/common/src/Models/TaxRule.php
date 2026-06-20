<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

class TaxRule extends BaseModel
{
    protected $table = 'tax_rules';

    protected $fillable = [
        'tax_class_id', 'tax_rate_id', 'based', 'priority', 'cross_border',
    ];

    protected $casts = [
        'cross_border' => 'boolean',
    ];
}
