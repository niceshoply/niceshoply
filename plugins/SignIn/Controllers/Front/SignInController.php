<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SignIn\Controllers\Front;

use Exception;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\SignIn\Services\SignInService;

class SignInController extends BaseController
{
    public function status(): mixed
    {
        $status = SignInService::getInstance()->status((int) token_customer_id());

        return json_success('ok', $status);
    }

    public function signIn(): mixed
    {
        try {
            $result = SignInService::getInstance()->signIn((int) token_customer_id());

            return json_success(__('SignIn::common.sign_success', ['points' => $result['points']]), $result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
