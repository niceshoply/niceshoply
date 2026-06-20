<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrivacyConsent\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\PrivacyConsent\Services\PrivacyConsentService;

class ConsentController extends BaseController
{
    public function store(Request $request): mixed
    {
        $choice = (string) $request->input('choice', 'accept');
        PrivacyConsentService::getInstance()->logChoice(
            $choice,
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        return json_success('ok');
    }
}
