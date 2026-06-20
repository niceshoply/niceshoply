<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnSocial\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;

class CnSocialController extends BaseController
{
    /**
     * 已启用的社交登录入口（前端渲染登录按钮）。
     */
    public function providers(): mixed
    {
        $providers = [];
        foreach (['weixin', 'qq', 'weibo'] as $provider) {
            if (! (bool) plugin_setting('cn_social', "{$provider}_enabled", false)) {
                continue;
            }
            $providers[] = [
                'provider'     => $provider,
                'label'        => __("CnSocial::common.{$provider}"),
                'redirect_url' => front_root_route('social.redirect', $provider),
            ];
        }

        return json_success('ok', $providers);
    }
}
