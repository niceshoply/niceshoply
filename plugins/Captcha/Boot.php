<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Captcha;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Captcha\Services\CaptchaService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 前台 head 注入人机验证脚本与配置
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return CaptchaService::getInstance()->renderHead();
        });
    }
}
