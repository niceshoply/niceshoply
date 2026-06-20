<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Captcha\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Captcha\Services\CaptchaService;

class CaptchaController extends BaseController
{
    /**
     * 前端获取验证配置（站点 key 与 provider）。
     */
    public function config(): mixed
    {
        $service = CaptchaService::getInstance();

        return json_success('ok', [
            'enabled'    => $service->configured(),
            'provider'   => $service->provider(),
            'site_key'   => $service->siteKey(),
            'script_url' => $service->scriptUrl(),
        ]);
    }

    /**
     * 服务端校验 token。
     */
    public function verify(Request $request): mixed
    {
        $token = (string) $request->input('token', $request->input('captcha_token', ''));
        $ok = CaptchaService::getInstance()->verify($token, (string) $request->ip());

        return $ok
            ? json_success(__('Captcha::common.passed'), ['passed' => true])
            : json_fail(__('Captcha::common.failed'));
    }
}
