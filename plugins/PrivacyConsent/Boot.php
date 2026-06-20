<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrivacyConsent;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\PrivacyConsent\Services\PrivacyConsentService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return PrivacyConsentService::getInstance()->render();
        });

        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'privacy_consent.index',
                'title'           => __('PrivacyConsent::common.menu'),
                'url'             => console_route('privacy_consent.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
