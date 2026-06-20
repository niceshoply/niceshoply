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
    'warehouse'         => '倉庫',
    'warehouses'        => '倉庫管理',
    'warehouse_code'    => '倉庫編碼',
    'warehouse_name'    => '倉庫名稱',
    'code'              => '編碼',
    'name'              => '名稱',
    'create_warehouse'  => '建立倉庫',
    'edit_warehouse'    => '編輯倉庫',
    'default_warehouse' => '預設倉庫',
    'is_default'        => '預設',
    'priority'          => '優先順序',
    'active'            => '啟用',
    'description'       => '描述',
    'contact_name'      => '聯絡人',
    'contact_phone'     => '聯絡電話',
    'country'           => '國家',
    'state'             => '省/州',
    'city'              => '城市',
    'address'           => '地址',
    'address_1'         => '地址1',
    'address_2'         => '地址2',
    'zipcode'           => '郵遞區號',
    'phone'             => '電話',
    'latitude'          => '緯度',
    'longitude'         => '經度',
    'all_warehouses'    => '所有倉庫',

    // Stock
    'stock'             => '庫存',
    'warehouse_stocks'  => '倉庫庫存',
    'sku_code'          => 'SKU編碼',
    'quantity'          => '數量',
    'reserved'          => '預留',
    'available'         => '可用',
    'low_threshold'     => '低庫存閾值',
    'adjust_stock'      => '調整庫存',
    'add_stock'         => '添加庫存',
    'adjust_quantity'   => '調整數量',
    'stock_adjusted'    => '庫存調整成功。',
    'adjust_hint'       => '正數為增加，負數為減少。',
    'import_stock'      => '匯入',
    'export_stock'      => '匯出',
    'download_template' => '下載範本',
    'import_success'    => '匯入完成：:success/:total 條成功。',
    'import_file'       => '選擇檔案',
    'import_file_hint'  => '支援 .xlsx、.csv 檔案。欄位：warehouse_id、sku_code、quantity。',

    // Stock Movements
    'stock_movements' => '庫存變動記錄',
    'type'            => '類型',
    'reference'       => '關聯單據',
    'note'            => '備註',
    'all_types'       => '所有類型',

    // Stock Transfers
    'stock_transfers' => '庫存調撥',
    'transfer_number' => '調撥單號',
    'from_warehouse'  => '來源倉庫',
    'to_warehouse'    => '目標倉庫',
    'create_transfer' => '建立調撥單',
    'transfer_detail' => '調撥單詳情',
    'items'           => '明細',
    'received'        => '已接收',
    'ship'            => '發貨',
    'complete'        => '完成',
    'status'          => '狀態',

    // Shipment
    'packages'          => '包裹',
    'package'           => '包裹',
    'ship_package'      => '包裹發貨',
    'express_company'   => '快遞公司',
    'express_number'    => '快遞單號',
    'all_shipped'       => '所有包裹已發貨。',
    'partially_shipped' => '部分發貨',

    // Allocation
    'allocation_strategy'   => '分配策略',
    'strategy_priority'     => '優先順序分配',
    'strategy_nearest'      => '就近倉庫',
    'strategy_stock_first'  => '庫存優先',
    'strategy_cost_optimal' => '成本最優',
    'allow_split_shipment'  => '允許拆單發貨',

    // Settings
    'warehouse_enabled'  => '啟用倉庫管理',
    'warehouse_settings' => '倉庫設定',
    'service_areas'      => '服務區域',
    'service_area_hint'  => '定義該倉庫服務的區域。留空表示全球覆蓋。',
    'all_states'         => '所有省份',
];
