<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'                => '采购补货',
    'enabled'             => '启用采购',
    'low_stock_threshold' => '低库存阈值',

    'saved'               => '已保存',
    'created'             => '采购单 :no 已创建',
    'received'            => '入库成功',
    'invalid_po'          => '采购单无效或已入库',
    'invalid_items'       => '明细格式有误',

    'title'               => '采购补货',
    'tip'                 => '管理供应商与采购单，低库存 SKU 自动生成补货建议。入库时若填写仓库 ID 且已安装 MultiWarehouse，则写入分仓库存；否则累加 SKU 总库存。明细格式：sku_id:数量:成本价，逗号分隔。',
    'suppliers'           => '供应商',
    'supplier_name'       => '供应商名称',
    'contact'             => '联系人',
    'phone'               => '电话',
    'email'               => '邮箱',
    'add_supplier'        => '添加供应商',
    'suggestions'         => '低库存补货建议',
    'sku_code'            => 'SKU',
    'current_qty'         => '当前库存',
    'suggest_qty'         => '建议补货',
    'create_po'           => '创建采购单',
    'supplier_id'         => '供应商ID',
    'warehouse_id'        => '入库仓库ID(可选)',
    'items'               => '明细(sku_id:qty:cost)',
    'remark'              => '备注',
    'orders'              => '采购单',
    'po_number'           => '单号',
    'status'              => '状态',
    'total'               => '金额',
    'receive'             => '入库',
    'no_data'             => '暂无数据',
    'confirm_receive'     => '确认入库？',
];
