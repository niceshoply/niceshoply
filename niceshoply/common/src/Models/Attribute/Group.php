<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Attribute;

use NiceShoply\Common\Models\BaseModel;
use NiceShoply\Common\Traits\Translatable;

class Group extends BaseModel
{
    use Translatable;

    protected $table = 'attribute_groups';

    protected $fillable = [
        'position',
    ];

    public function getForeignKey(): string
    {
        return 'attribute_group_id';
    }
}
