<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // 每个推荐坑位默认返回数量
    ['name' => 'limit', 'label_key' => 'common.limit', 'type' => 'string', 'default' => '10', 'rules' => 'nullable|integer|min:1|max:50'],
    // 推荐不足时是否用热销商品兜底
    ['name' => 'fallback_hot', 'label_key' => 'common.fallback_hot', 'type' => 'switch', 'default' => true],
    // 最近浏览每人保留的最大条数
    ['name' => 'recent_keep', 'label_key' => 'common.recent_keep', 'type' => 'string', 'default' => '50', 'rules' => 'nullable|integer|min:1'],
];
