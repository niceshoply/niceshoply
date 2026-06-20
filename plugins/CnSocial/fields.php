<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // 微信开放平台(网站应用)
    ['name' => 'weixin_enabled', 'label_key' => 'common.weixin_enabled', 'type' => 'select', 'emptyOption' => false, 'required' => true,
        'options' => [['value' => '1', 'label_key' => 'common.yes'], ['value' => '0', 'label_key' => 'common.no']]],
    ['name' => 'weixin_app_id', 'label_key' => 'common.weixin_app_id', 'type' => 'string', 'required' => false],
    ['name' => 'weixin_app_secret', 'label_key' => 'common.weixin_app_secret', 'type' => 'string', 'required' => false],

    // QQ 互联
    ['name' => 'qq_enabled', 'label_key' => 'common.qq_enabled', 'type' => 'select', 'emptyOption' => false, 'required' => true,
        'options' => [['value' => '1', 'label_key' => 'common.yes'], ['value' => '0', 'label_key' => 'common.no']]],
    ['name' => 'qq_app_id', 'label_key' => 'common.qq_app_id', 'type' => 'string', 'required' => false],
    ['name' => 'qq_app_secret', 'label_key' => 'common.qq_app_secret', 'type' => 'string', 'required' => false],

    // 微博开放平台
    ['name' => 'weibo_enabled', 'label_key' => 'common.weibo_enabled', 'type' => 'select', 'emptyOption' => false, 'required' => true,
        'options' => [['value' => '1', 'label_key' => 'common.yes'], ['value' => '0', 'label_key' => 'common.no']]],
    ['name' => 'weibo_app_id', 'label_key' => 'common.weibo_app_id', 'type' => 'string', 'required' => false],
    ['name' => 'weibo_app_secret', 'label_key' => 'common.weibo_app_secret', 'type' => 'string', 'required' => false],
];
