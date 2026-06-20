<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiAssistant\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\AiAssistant\Services\AiAssistantService;
use Throwable;

class AssistantController extends BaseController
{
    public function chat(Request $request): mixed
    {
        $service = AiAssistantService::getInstance();
        if (! $service->enabled()) {
            return json_fail(__('AiAssistant::common.disabled'));
        }

        $message = (string) $request->input('message', '');
        $key = token_customer_id() ? 'c:'.token_customer_id() : 'ip:'.$request->ip();

        try {
            $reply = $service->answer($message, $key);

            return json_success('ok', ['reply' => $reply]);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }
}
