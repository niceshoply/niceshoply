<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'         => '商品导入导出',
    'export_title' => '导出商品',
    'export_desc'  => '导出全部商品 SKU 的价格、库存与上下架状态为 CSV（UTF-8）。',
    'export_btn'   => '下载 CSV',

    'import_title' => '导入更新',
    'import_desc'  => '上传 CSV，按 sku_code 回填更新 price/quantity；勾选后同步上下架(active)。表头需含 sku_code 列。',
    'apply_active' => '同时更新上下架(active)',
    'import_btn'   => '上传并更新',
    'import_done'  => '导入完成：更新 :updated 条，跳过 :skipped 条',

    'columns'      => '列说明',
    'col_spu'      => 'spu_code：商品编码（导出用，导入忽略）',
    'col_sku'      => 'sku_code：SKU 编码（导入匹配键，必填）',
    'col_name'     => 'name：商品名称（导出用，导入忽略）',
    'col_price'    => 'price：SKU 价格',
    'col_qty'      => 'quantity：SKU 库存',
    'col_active'   => 'active：1 上架 / 0 下架',
    'note'         => '注意：导入仅更新已存在的 SKU，不会新建商品。',
    'result'       => '本次结果',
];
