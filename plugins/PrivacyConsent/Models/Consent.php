<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrivacyConsent\Models;

use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    protected $table = 'privacy_consents';

    public $timestamps = false;

    protected $guarded = [];
}
