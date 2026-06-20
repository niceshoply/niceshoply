<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatMp\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\WechatMp\Services\WechatMpService;

class WechatMpController extends BaseController
{
    /**
     * 小程序登录：传入 wx.login 返回的 code，换取会员 token。
     */
    public function miniLogin(Request $request): mixed
    {
        try {
            $code  = (string) $request->input('code');
            $token = WechatMpService::getInstance()->miniLogin($code, $request->header('User-Agent'));

            return json_success('ok', $token);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * JS-SDK 配置（公众号网页分享等）。
     */
    public function jsSdk(Request $request): mixed
    {
        try {
            $url = (string) $request->input('url', $request->headers->get('referer', ''));
            $config = WechatMpService::getInstance()->jsSdkConfig($url);

            return json_success('ok', $config);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
