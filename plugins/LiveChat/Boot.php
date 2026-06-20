<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LiveChat;

use Plugin\LiveChat\Services\LiveChatService;

class Boot
{
    public function init(): void
    {
        listen_blade_insert('front.layout.app.head.bottom', function () {
            return LiveChatService::getInstance()->renderWidget();
        });
    }
}
