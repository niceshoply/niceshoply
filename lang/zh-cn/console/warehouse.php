<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // Warehouse
    'warehouse'         => '仓库',
    'warehouses'        => '仓库管理',
    'warehouse_code'    => '仓库编码',
    'warehouse_name'    => '仓库名称',
    'code'              => '编码',
    'name'              => '名称',
    'create_warehouse'  => '创建仓库',
    'edit_warehouse'    => '编辑仓库',
    'default_warehouse' => '默认仓库',
    'is_default'        => '默认',
    'priority'          => '优先级',
    'active'            => '启用',
    'description'       => '描述',
    'contact_name'      => '联系人',
    'contact_phone'     => '联系电话',
    'country'           => '国家',
    'state'             => '省/州',
    'city'              => '城市',
    'address'           => '地址',
    'address_1'         => '地址1',
    'address_2'         => '地址2',
    'zipcode'           => '邮编',
    'phone'             => '电话',
    'latitude'          => '纬度',
    'longitude'         => '经度',
    'all_warehouses'    => '全部仓库',

    // Stock
    'stock'             => '库存',
    'warehouse_stocks'  => '仓库库存',
    'sku_code'          => 'SKU编码',
    'quantity'          => '数量',
    'reserved'          => '预留',
    'available'         => '可用',
    'low_threshold'     => '低库存阈值',
    'adjust_stock'      => '调整库存',
    'add_stock'         => '添加库存',
    'adjust_quantity'   => '调整数量',
    'stock_adjusted'    => '库存调整成功。',
    'adjust_hint'       => '正数为增加，负数为减少。',
    'import_stock'      => '导入',
    'export_stock'      => '导出',
    'download_template' => '下载模板',
    'import_success'    => '导入完成：:success/:total 条成功。',
    'import_dispatched' => '导入任务已加入队列，将在后台处理。',
    'import_file'       => '选择文件',
    'import_file_hint'  => '支持 .xlsx、.csv 文件。列：warehouse_id、sku_code、quantity。',

    // Stock Movements
    'stock_movements' => '库存变动记录',
    'type'            => '类型',
    'reference'       => '关联单据',
    'note'            => '备注',
    'all_types'       => '全部类型',

    // Stock Transfers
    'stock_transfers' => '库存调拨',
    'transfer_number' => '调拨单号',
    'from_warehouse'  => '源仓库',
    'to_warehouse'    => '目标仓库',
    'create_transfer' => '创建调拨单',
    'transfer_detail' => '调拨单详情',
    'items'           => '明细',
    'received'        => '已接收',
    'ship'            => '发货',
    'complete'        => '完成',
    'status'          => '状态',

    // Shipment
    'packages'          => '包裹',
    'package'           => '包裹',
    'ship_package'      => '包裹发货',
    'express_company'   => '快递公司',
    'express_number'    => '快递单号',
    'all_shipped'       => '所有包裹已发货。',
    'partially_shipped' => '部分发货',

    // Allocation
    'allocation_strategy'   => '分配策略',
    'strategy_priority'     => '优先级分配',
    'strategy_nearest'      => '就近仓库',
    'strategy_stock_first'  => '库存优先',
    'strategy_cost_optimal' => '成本最优',
    'allow_split_shipment'  => '允许拆单发货',

    // Settings
    'warehouse_enabled'  => '启用仓库管理',
    'warehouse_settings' => '仓库设置',
    'service_areas'      => '服务区域',
    'service_area_hint'  => '定义该仓库服务的区域。留空表示全球覆盖。',
    'all_states'         => '所有省份',
];
