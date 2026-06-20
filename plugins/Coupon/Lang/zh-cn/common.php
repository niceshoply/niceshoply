<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu_title'         => '优惠券',
    'enabled'            => '启用优惠券',
    'allow_stack'        => '允许与其他优惠叠加',
    'yes'                => '是',
    'no'                 => '否',

    'code'               => '券码',
    'name'               => '券名称',
    'type'               => '类型',
    'type_fixed'         => '满减券',
    'type_percent'       => '折扣券',
    'type_free_shipping' => '免运费券',
    'value'              => '面值/折扣',
    'min_amount'         => '使用门槛(最低订单金额)',
    'max_discount'       => '最大优惠金额(0 不限)',
    'usage_limit'        => '总可用次数(0 不限)',
    'used_count'         => '已用次数',
    'per_customer_limit' => '每人限用(0 不限)',
    'start_at'           => '生效时间',
    'end_at'             => '失效时间',
    'active'             => '启用',

    'discount_title'     => '优惠券抵扣',
    'create'             => '新建优惠券',
    'edit'               => '编辑优惠券',
    'saved'              => '保存成功',
    'deleted'            => '删除成功',
    'confirm_delete'     => '确定删除该优惠券？',
    'no_data'            => '暂无优惠券',
    'keyword'            => '搜索券码/名称',
    'search'             => '搜索',
    'actions'            => '操作',

    // 校验/前台提示
    'applied'            => '优惠券已应用',
    'removed'            => '优惠券已移除',
    'code_required'      => '请输入券码',
    'invalid'            => '优惠券无效',
    'not_started'        => '优惠券尚未生效',
    'expired'            => '优惠券已过期',
    'used_up'            => '优惠券已被领完/用完',
    'err_min_amount'     => '订单金额需满 :amount 才可使用',
    'err_per_customer'   => '您已达到该优惠券的使用次数上限',
];
