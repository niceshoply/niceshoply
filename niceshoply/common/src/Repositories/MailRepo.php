<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

class MailRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @return array[]
     */
    public function getEngines(): array
    {
        $engines = [
            ['code' => '', 'name' => 'None', 'value' => 'none'],
            ['code' => 'smtp', 'name' => 'SMTP', 'value' => 'smtp'],
            ['code' => 'log', 'name' => 'Log', 'value' => 'log'],
        ];

        return fire_hook_filter('common.repo.mail.engines', $engines);
    }
}
