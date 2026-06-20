<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'      => 'charge_mode',
        'label_key' => 'common.charge_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => 'weight', 'label_key' => 'common.mode_weight'],
            ['value' => 'piece', 'label_key' => 'common.mode_piece'],
            ['value' => 'amount', 'label_key' => 'common.mode_amount'],
        ],
        'required'    => true,
        'emptyOption' => false,
        'rules'       => 'required',
    ],
    // 按重量
    ['name' => 'first_weight', 'label_key' => 'common.first_weight', 'type' => 'string', 'required' => false],
    ['name' => 'first_fee', 'label_key' => 'common.first_fee', 'type' => 'string', 'required' => false],
    ['name' => 'extra_unit', 'label_key' => 'common.extra_unit', 'type' => 'string', 'required' => false],
    ['name' => 'extra_fee', 'label_key' => 'common.extra_fee', 'type' => 'string', 'required' => false],
    // 按件数
    ['name' => 'first_piece', 'label_key' => 'common.first_piece', 'type' => 'string', 'required' => false],
    ['name' => 'first_piece_fee', 'label_key' => 'common.first_piece_fee', 'type' => 'string', 'required' => false],
    ['name' => 'extra_piece_fee', 'label_key' => 'common.extra_piece_fee', 'type' => 'string', 'required' => false],
    // 固定金额
    ['name' => 'flat_fee', 'label_key' => 'common.flat_fee', 'type' => 'string', 'required' => false],
    // 通用
    ['name' => 'free_threshold', 'label_key' => 'common.free_threshold', 'type' => 'string', 'required' => false],
    ['name' => 'remote_surcharge', 'label_key' => 'common.remote_surcharge', 'type' => 'string', 'required' => false],
    ['name' => 'remote_keywords', 'label_key' => 'common.remote_keywords', 'type' => 'string', 'required' => false],
];
