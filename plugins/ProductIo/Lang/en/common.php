<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'         => 'Product I/O',
    'export_title' => 'Export products',
    'export_desc'  => 'Export all product SKUs (price, stock, active status) as CSV (UTF-8).',
    'export_btn'   => 'Download CSV',

    'import_title' => 'Import & update',
    'import_desc'  => 'Upload a CSV to update price/quantity by sku_code; optionally sync active. Header must include sku_code.',
    'apply_active' => 'Also update active status',
    'import_btn'   => 'Upload & update',
    'import_done'  => 'Done: updated :updated, skipped :skipped',

    'columns'      => 'Columns',
    'col_spu'      => 'spu_code: product code (export only, ignored on import)',
    'col_sku'      => 'sku_code: SKU code (match key, required)',
    'col_name'     => 'name: product name (export only, ignored on import)',
    'col_price'    => 'price: SKU price',
    'col_qty'      => 'quantity: SKU stock',
    'col_active'   => 'active: 1 on / 0 off',
    'note'         => 'Note: import only updates existing SKUs; it does not create products.',
    'result'       => 'Result',
];
