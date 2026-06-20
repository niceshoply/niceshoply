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
    ['name' => 'batch_size', 'label_key' => 'common.batch_size', 'type' => 'string', 'default' => '50', 'rules' => 'nullable|integer|min:1|max:500'],
];
