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
    // 每人每张券限领次数
    ['name' => 'claim_limit', 'label_key' => 'common.claim_limit', 'type' => 'string', 'default' => '1', 'rules' => 'nullable|integer|min:1'],
];
