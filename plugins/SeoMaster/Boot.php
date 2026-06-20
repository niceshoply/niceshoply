<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SeoMaster;

use Plugin\SeoMaster\Services\SeoService;

class Boot
{
    public function init(): void
    {
        // 向前台 <head> 底部注入 SEO 标签
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return SeoService::getInstance()->renderHead();
        });
    }
}
