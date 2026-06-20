<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // 保留最近 N 份备份，超出自动删除最旧的
    ['name' => 'keep_count', 'label_key' => 'common.keep_count', 'type' => 'string', 'default' => '10', 'rules' => 'nullable|integer|min:1'],
    // mysqldump 可执行文件路径（留空使用 PATH 中的 mysqldump）
    ['name' => 'mysqldump_path', 'label_key' => 'common.mysqldump_path', 'type' => 'string', 'required' => false],
];
