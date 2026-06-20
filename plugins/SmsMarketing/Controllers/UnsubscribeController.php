<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmsMarketing\Controllers;

use Illuminate\Http\Request;
use Plugin\SmsMarketing\Services\SmsMarketingService;

class UnsubscribeController
{
    public function unsubscribe(Request $request): mixed
    {
        $mobile = (string) $request->query('mobile', '');
        SmsMarketingService::getInstance()->unsubscribe($mobile);

        return response(__('SmsMarketing::common.unsubscribed'));
    }
}
