<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'zones'            => '配送区域',
    'zone_name'        => '区域名称',
    'country_ids'      => '国家ID',
    'country_ids_hint' => '逗号分隔的国家ID，留空表示全部国家',
    'state_ids'        => '省/州ID',
    'state_ids_hint'   => '逗号分隔的省/州ID，留空表示区域内全部',
    'priority'         => '优先级',

    'templates'           => '运费模板',
    'template_name'       => '模板名称',
    'zone'                => '配送区域',
    'all_zones'           => '全区域',
    'calc_type'           => '计费方式',
    'calc_flat'           => '固定运费',
    'calc_by_weight'      => '按重量',
    'calc_by_qty'         => '按件数',
    'calc_by_amount'      => '按金额',
    'rules'               => '计费规则(JSON)',
    'rules_hint'          => '示例：{"base":5,"rate":2,"unit":1} 或 {"tiers":[{"max":1,"cost":5},{"max":5,"cost":12}]}',
    'free_threshold'      => '满额包邮门槛',
    'free_threshold_hint' => '订单小计达到该金额免运费，0=不包邮',
];
