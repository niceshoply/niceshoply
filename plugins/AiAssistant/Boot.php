<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiAssistant;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\AiAssistant\Services\AiAssistantService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return AiAssistantService::getInstance()->renderWidget();
        });

        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'ai_assistant.kb',
                'title'           => __('AiAssistant::common.menu_kb'),
                'url'             => console_route('ai_assistant.kb'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'ai_assistant.conversations',
                'title'           => __('AiAssistant::common.menu_log'),
                'url'             => console_route('ai_assistant.conversations'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
