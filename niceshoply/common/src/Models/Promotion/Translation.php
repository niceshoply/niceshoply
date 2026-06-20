<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Promotion;

use NiceShoply\Common\Models\BaseModel;

/**
 * 促销活动翻译模型
 */
class Translation extends BaseModel
{
    protected $table = 'nice_promotion_translations';

    public $timestamps = false;

    protected $fillable = [
        'promotion_id', 'locale', 'label', 'description',
    ];
}
