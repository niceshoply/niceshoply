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
    'warehouse'         => 'Warehouse',
    'warehouses'        => 'Warehouses',
    'warehouse_code'    => 'Warehouse Code',
    'warehouse_name'    => 'Warehouse Name',
    'code'              => 'Code',
    'name'              => 'Name',
    'create_warehouse'  => 'Create Warehouse',
    'edit_warehouse'    => 'Edit Warehouse',
    'default_warehouse' => 'Default Warehouse',
    'is_default'        => 'Default',
    'priority'          => 'Priority',
    'active'            => 'Active',
    'description'       => 'Description',
    'contact_name'      => 'Contact Name',
    'contact_phone'     => 'Contact Phone',
    'country'           => 'Country',
    'state'             => 'State/Province',
    'city'              => 'City',
    'address'           => 'Address',
    'address_1'         => 'Address Line 1',
    'address_2'         => 'Address Line 2',
    'zipcode'           => 'Zip Code',
    'phone'             => 'Phone',
    'latitude'          => 'Latitude',
    'longitude'         => 'Longitude',
    'all_warehouses'    => 'All Warehouses',

    // Stock
    'stock'             => 'Stock',
    'warehouse_stocks'  => 'Warehouse Stocks',
    'sku_code'          => 'SKU Code',
    'quantity'          => 'Quantity',
    'reserved'          => 'Reserved',
    'available'         => 'Available',
    'low_threshold'     => 'Low Stock Threshold',
    'adjust_stock'      => 'Adjust Stock',
    'add_stock'         => 'Add Stock',
    'adjust_quantity'   => 'Adjust Quantity',
    'stock_adjusted'    => 'Stock adjusted successfully.',
    'adjust_hint'       => 'Positive number to add, negative to subtract.',
    'import_stock'      => 'Import',
    'export_stock'      => 'Export',
    'download_template' => 'Download Template',
    'import_success'    => 'Import completed: :success/:total succeeded.',
    'import_dispatched' => 'Import task has been queued and will be processed in the background.',
    'import_file'       => 'Select File',
    'import_file_hint'  => 'Supports .xlsx, .csv files. Columns: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Stock Movements',
    'type'            => 'Type',
    'reference'       => 'Reference',
    'note'            => 'Note',
    'all_types'       => 'All Types',

    // Stock Transfers
    'stock_transfers' => 'Stock Transfers',
    'transfer_number' => 'Transfer Number',
    'from_warehouse'  => 'From Warehouse',
    'to_warehouse'    => 'To Warehouse',
    'create_transfer' => 'Create Transfer',
    'transfer_detail' => 'Transfer Detail',
    'items'           => 'Items',
    'received'        => 'Received',
    'ship'            => 'Ship',
    'complete'        => 'Complete',
    'status'          => 'Status',

    // Shipment
    'packages'          => 'Packages',
    'package'           => 'Package',
    'ship_package'      => 'Ship Package',
    'express_company'   => 'Express Company',
    'express_number'    => 'Tracking Number',
    'all_shipped'       => 'All packages have been shipped.',
    'partially_shipped' => 'Partially Shipped',

    // Allocation
    'allocation_strategy'   => 'Allocation Strategy',
    'strategy_priority'     => 'Priority Based',
    'strategy_nearest'      => 'Nearest Warehouse',
    'strategy_stock_first'  => 'Stock First',
    'strategy_cost_optimal' => 'Cost Optimal',
    'allow_split_shipment'  => 'Allow Split Shipment',

    // Settings
    'warehouse_enabled'  => 'Enable Warehouse Management',
    'warehouse_settings' => 'Warehouse Settings',
    'service_areas'      => 'Service Areas',
    'service_area_hint'  => 'Define which regions this warehouse serves. Leave empty for global coverage.',
    'all_states'         => 'All States/Provinces',
];
