<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmsMarketing\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    protected $table = 'sms_campaigns';

    protected $guarded = [];

    protected $casts = [
        'template_data' => 'array',
        'sent_at'     => 'datetime',
    ];

    public const STATUS_DRAFT   = 'draft';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT    = 'sent';
}
