<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatMp\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    protected $table = 'wechat_auto_replies';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
