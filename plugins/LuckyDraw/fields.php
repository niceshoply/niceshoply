<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'switch', 'default' => true],
    ['name' => 'title', 'label_key' => 'common.activity_title', 'type' => 'string', 'default' => '幸运大转盘'],
    // 每人每日抽奖次数
    ['name' => 'draws_per_day', 'label_key' => 'common.draws_per_day', 'type' => 'string', 'default' => '1', 'rules' => 'nullable|integer|min:1'],
];
