<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Controllers;

use App\Http\Controllers\Controller;
use Plugin\EmailMarketing\Services\EmailMarketingService;

class UnsubscribeController extends Controller
{
    public function unsubscribe(string $token)
    {
        $ok = EmailMarketingService::getInstance()->unsubscribeByToken($token);

        $message = $ok ? __('EmailMarketing::common.unsub_ok') : __('EmailMarketing::common.unsub_fail');

        return response(
            '<!doctype html><html><head><meta charset="utf-8"><title>'.$message.'</title></head>'
            .'<body style="font-family:sans-serif;text-align:center;padding:60px"><h2>'.$message.'</h2></body></html>'
        );
    }
}
