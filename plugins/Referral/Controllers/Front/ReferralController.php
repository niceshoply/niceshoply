<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Referral\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Referral\Services\ReferralService;

class ReferralController extends BaseController
{
    public function info(): mixed
    {
        $customerId = (int) (token_customer_id() ?? 0);
        if ($customerId <= 0) {
            return json_fail(__('Referral::common.need_login'));
        }

        return json_success('ok', ReferralService::getInstance()->stats($customerId));
    }
}
