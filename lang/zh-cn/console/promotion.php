<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'promotions'           => '促销活动',
    'name'                 => '活动名称',
    'name_hint'            => '内部标识，便于管理识别',
    'label'                => '展示标题',
    'label_hint'           => '面向顾客展示，如「全场满 200 减 30」',
    'description'          => '展示描述',
    'scope'                => '作用域',
    'scope_cart'           => '整单',
    'scope_product'        => '指定商品',
    'rule'                 => '优惠规则',
    'condition_type'       => '条件类型',
    'condition_none'       => '无条件',
    'condition_min_amount' => '满额',
    'condition_min_qty'    => '满件',
    'condition_tiered'     => '阶梯',
    'condition_value'      => '条件阈值',
    'condition_value_hint' => '满额填金额；满件填件数',
    'tiers'                => '阶梯规则',
    'tiers_hint'           => '每行一条「门槛:优惠值」，例如 200:30',
    'action_type'          => '优惠类型',
    'action_percent'       => '百分比折扣',
    'action_fixed'         => '固定减免',
    'action_free_shipping' => '免运费',
    'action_value'         => '优惠值',
    'action_value_hint'    => '百分比填 0-100；固定减免填金额',
    'action_max'           => '折扣封顶',
    'action_max_hint'      => '百分比折扣的最高减免金额，0=不限',
    'priority'             => '优先级',
    'exclusive'            => '互斥（命中后不再叠加）',
    'usage_limit'          => '总次数上限',
    'per_customer_limit'   => '单客户上限',
    'used'                 => '已用',
    'starts_at'            => '开始时间',
    'ends_at'              => '结束时间',
];
