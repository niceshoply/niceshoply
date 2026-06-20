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
    ['name' => 'inviter_points', 'label_key' => 'common.inviter_points', 'type' => 'string', 'default' => '100'],
    ['name' => 'invitee_points', 'label_key' => 'common.invitee_points', 'type' => 'string', 'default' => '50'],
    ['name' => 'inviter_order_points', 'label_key' => 'common.inviter_order_points', 'type' => 'string', 'default' => '200'],
    // 邀请人注册奖励券(coupons 表 ID，0 不发放)
    ['name' => 'inviter_coupon_id', 'label_key' => 'common.inviter_coupon_id', 'type' => 'string', 'default' => '0'],
];
