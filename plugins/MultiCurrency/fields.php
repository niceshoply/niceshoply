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
    ['name' => 'rate_api_url', 'label_key' => 'common.rate_api_url', 'type' => 'string', 'default' => 'https://api.exchangerate-api.com/v4/latest/USD'],
    ['name' => 'base_currency', 'label_key' => 'common.base_currency', 'type' => 'string', 'default' => 'usd'],
];
