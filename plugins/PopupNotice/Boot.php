<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PopupNotice;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\PopupNotice\Services\NoticeService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 注入前台弹窗/公告
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return NoticeService::getInstance()->render();
        });

        // 后台「内容」菜单入口
        listen_hook_filter('console.component.sidebar.content.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'popup_notice.index',
                'title'           => __('PopupNotice::common.menu'),
                'url'             => console_route('popup_notice.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
