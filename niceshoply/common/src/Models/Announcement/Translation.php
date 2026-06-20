<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Announcement;

use NiceShoply\Common\Models\BaseModel;

/**
 * 公告翻译模型
 */
class Translation extends BaseModel
{
    protected $table = 'announcement_translations';

    public $timestamps = false;

    protected $fillable = [
        'announcement_id', 'locale', 'text',
    ];
}
