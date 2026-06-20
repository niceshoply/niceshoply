<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => '卡券核销',
    'enabled'        => '启用核销',
    'staff_token'    => '核销员令牌(核销 API 需传 staff_token)',

    'invalid'        => '核销码无效',
    'already_redeemed' => '已核销',
    'expired'        => '已过期',
    'disabled'       => '核销功能已关闭',
    'invalid_staff'  => '核销员令牌无效',
    'redeemed_ok'    => '核销成功',
    'generated'      => '核销码已生成',

    'title'          => '到店卡券核销',
    'tip'            => '生成核销码供顾客到店出示；核销员 App 调用 verify 校验、use 核销(需 staff_token)。',
    'title_label'    => '券名称',
    'type'           => '类型',
    'customer_id'    => '会员ID(可选)',
    'generate'       => '生成核销码',
    'code'           => '核销码',
    'status'         => '状态',
    'redeemed_at'    => '核销时间',
    'no_data'        => '暂无记录',
    'api_title'      => '核销 API（前缀 /api/v1）',
];
