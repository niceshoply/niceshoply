<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models\MemberLevel;

use NiceShoply\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'nice_member_level_translations';

    public $timestamps = false;

    protected $fillable = ['member_level_id', 'locale', 'label', 'description'];
}
