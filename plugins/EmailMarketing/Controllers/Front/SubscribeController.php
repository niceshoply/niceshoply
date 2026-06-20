<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\EmailMarketing\Services\EmailMarketingService;

class SubscribeController extends BaseController
{
    public function subscribe(Request $request): mixed
    {
        try {
            $customerId = (int) token_customer_id();
            $email = (string) $request->input('email');
            EmailMarketingService::getInstance()->subscribe($email, $customerId);

            return json_success(__('EmailMarketing::common.subscribed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
