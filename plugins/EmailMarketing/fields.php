<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // 每批发送数量（避免触发邮件服务商限流）
    [
        'name'      => 'batch_size',
        'label_key' => 'common.batch_size',
        'type'      => 'string',
        'required'  => false,
    ],
    // 发件人名称（留空使用系统默认）
    [
        'name'      => 'from_name',
        'label_key' => 'common.from_name',
        'type'      => 'string',
        'required'  => false,
    ],
];
