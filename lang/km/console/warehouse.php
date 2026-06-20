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
    'warehouse'         => 'ឃ្លាំង',
    'warehouses'        => 'គ្រប់គ្រងឃ្លាំង',
    'warehouse_code'    => 'លេខកូដឃ្លាំង',
    'warehouse_name'    => 'ឈ្មោះឃ្លាំង',
    'code'              => 'លេខកូដ',
    'name'              => 'ឈ្មោះ',
    'create_warehouse'  => 'បង្កើតឃ្លាំង',
    'edit_warehouse'    => 'កែសម្រួលឃ្លាំង',
    'default_warehouse' => 'ឃ្លាំងលំនាំដើម',
    'is_default'        => 'លំនាំដើម',
    'priority'          => 'អាទិភាព',
    'active'            => 'សកម្ម',
    'description'       => 'ការពិពណ៌នា',
    'contact_name'      => 'ឈ្មោះទំនាក់ទំនង',
    'contact_phone'     => 'ទូរសព្ទទំនាក់ទំនង',
    'country'           => 'ប្រទេស',
    'state'             => 'ខេត្ត/ក្រុង',
    'city'              => 'ទីក្រុង',
    'address'           => 'អាសយដ្ឋាន',
    'address_1'         => 'អាសយដ្ឋាន ១',
    'address_2'         => 'អាសយដ្ឋាន ២',
    'zipcode'           => 'លេខកូដប្រៃសណីយ៍',
    'phone'             => 'ទូរសព្ទ',
    'latitude'          => 'រយទទឹង',
    'longitude'         => 'រយបណ្តោយ',
    'all_warehouses'    => 'ឃ្លាំងទាំងអស់',

    // Stock
    'stock'             => 'ស្តុក',
    'warehouse_stocks'  => 'ស្តុកឃ្លាំង',
    'sku_code'          => 'លេខកូដ SKU',
    'quantity'          => 'បរិមាណ',
    'reserved'          => 'បានកក់',
    'available'         => 'មាន',
    'low_threshold'     => 'កម្រិតស្តុកទាប',
    'adjust_stock'      => 'កែតម្រូវស្តុក',
    'add_stock'         => 'បន្ថែមស្តុក',
    'adjust_quantity'   => 'កែតម្រូវបរិមាណ',
    'stock_adjusted'    => 'ស្តុកត្រូវបានកែតម្រូវដោយជោគជ័យ។',
    'adjust_hint'       => 'លេខវិជ្ជមានដើម្បីបន្ថែម លេខអវិជ្ជមានដើម្បីដក។',
    'import_stock'      => 'នាំចូល',
    'export_stock'      => 'នាំចេញ',
    'download_template' => 'ទាញយកគំរូ',
    'import_success'    => 'នាំចូលបានបញ្ចប់: :success/:total ជោគជ័យ។',
    'import_file'       => 'ជ្រើសរើសឯកសារ',
    'import_file_hint'  => 'គាំទ្រឯកសារ .xlsx, .csv។ ជួរឈរ: warehouse_id, sku_code, quantity។',

    // Stock Movements
    'stock_movements' => 'ចលនាស្តុក',
    'type'            => 'ប្រភេទ',
    'reference'       => 'ឯកសារយោង',
    'note'            => 'កំណត់ចំណាំ',
    'all_types'       => 'ប្រភេទទាំងអស់',

    // Stock Transfers
    'stock_transfers' => 'ផ្ទេរស្តុក',
    'transfer_number' => 'លេខផ្ទេរ',
    'from_warehouse'  => 'ពីឃ្លាំង',
    'to_warehouse'    => 'ទៅឃ្លាំង',
    'create_transfer' => 'បង្កើតការផ្ទេរ',
    'transfer_detail' => 'ព័ត៌មានលម្អិតការផ្ទេរ',
    'items'           => 'ធាតុ',
    'received'        => 'បានទទួល',
    'ship'            => 'ដឹកជញ្ជូន',
    'complete'        => 'បានបញ្ចប់',
    'status'          => 'ស្ថានភាព',

    // Shipment
    'packages'          => 'កញ្ចប់',
    'package'           => 'កញ្ចប់',
    'ship_package'      => 'ដឹកជញ្ជូនកញ្ចប់',
    'express_company'   => 'ក្រុមហ៊ុនដឹកជញ្ជូន',
    'express_number'    => 'លេខតាមដាន',
    'all_shipped'       => 'កញ្ចប់ទាំងអស់ត្រូវបានដឹកជញ្ជូន។',
    'partially_shipped' => 'ដឹកជញ្ជូនដោយផ្នែក',

    // Allocation
    'allocation_strategy'   => 'យុទ្ធសាស្ត្រចែកចាយ',
    'strategy_priority'     => 'តាមអាទិភាព',
    'strategy_nearest'      => 'ឃ្លាំងជិតបំផុត',
    'strategy_stock_first'  => 'ស្តុកមុន',
    'strategy_cost_optimal' => 'ថ្លៃដើមល្អបំផុត',
    'allow_split_shipment'  => 'អនុញ្ញាតការបែងចែកការដឹកជញ្ជូន',

    // Settings
    'warehouse_enabled'  => 'បើកការគ្រប់គ្រងឃ្លាំង',
    'warehouse_settings' => 'ការកំណត់ឃ្លាំង',
    'service_areas'      => 'តំបន់សេវាកម្ម',
    'service_area_hint'  => 'កំណត់តំបន់ដែលឃ្លាំងនេះផ្តល់សេវា។ ទុកទទេសម្រាប់គ្របដណ្តប់ទូទាំងពិភពលោក។',
    'all_states'         => 'គ្រប់ខេត្ត/ក្រុង',
];
