<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

// PayPal 插件后台可配置字段定义。
// 说明：client_id / client_secret 在 PayPal 开发者后台「My Apps & Credentials」获取；
// webhook_id 在「Webhooks」中创建 Webhook 后获得，用于回调签名校验（强烈建议配置）。
return [
    [
        // 运行环境：sandbox 沙盒（联调测试）/ live 生产
        'name'      => 'mode',
        'label_key' => 'common.mode',
        'type'      => 'select',
        'options'   => [
            ['value' => 'sandbox', 'label_key' => 'common.mode_sandbox'],
            ['value' => 'live', 'label_key' => 'common.mode_live'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    [
        // PayPal 应用 Client ID
        'name'      => 'client_id',
        'label_key' => 'common.client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:10',
    ],
    [
        // PayPal 应用 Client Secret
        'name'      => 'client_secret',
        'label_key' => 'common.client_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:10',
    ],
    [
        // Webhook ID：用于校验 PayPal 回调签名，未配置时回调将被拒绝（fail closed）
        'name'      => 'webhook_id',
        'label_key' => 'common.webhook_id',
        'type'      => 'string',
        'required'  => false,
    ],
];
