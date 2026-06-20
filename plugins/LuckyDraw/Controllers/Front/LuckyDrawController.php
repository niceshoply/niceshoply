<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LuckyDraw\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\LuckyDraw\Services\LuckyDrawService;
use Throwable;

class LuckyDrawController extends BaseController
{
    public function info(): mixed
    {
        $customerId = (int) (token_customer_id() ?? 0);
        $service = LuckyDrawService::getInstance();

        return json_success('ok', [
            'title'     => (string) plugin_setting('lucky_draw', 'title', ''),
            'enabled'   => $service->enabled(),
            'prizes'    => $service->activePrizes(),
            'remaining' => $service->remainingDraws($customerId),
        ]);
    }

    public function draw(): mixed
    {
        try {
            $customerId = (int) (token_customer_id() ?? 0);
            $result = LuckyDrawService::getInstance()->draw($customerId);

            return json_success('ok', $result);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }
}
