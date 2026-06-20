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
    'warehouse'         => 'Kho hàng',
    'warehouses'        => 'Quản lý kho hàng',
    'warehouse_code'    => 'Mã kho',
    'warehouse_name'    => 'Tên kho',
    'code'              => 'Mã',
    'name'              => 'Tên',
    'create_warehouse'  => 'Tạo kho hàng',
    'edit_warehouse'    => 'Sửa kho hàng',
    'default_warehouse' => 'Kho mặc định',
    'is_default'        => 'Mặc định',
    'priority'          => 'Ưu tiên',
    'active'            => 'Kích hoạt',
    'description'       => 'Mô tả',
    'contact_name'      => 'Tên liên hệ',
    'contact_phone'     => 'Điện thoại liên hệ',
    'country'           => 'Quốc gia',
    'state'             => 'Tỉnh/Thành phố',
    'city'              => 'Quận/Huyện',
    'address'           => 'Địa chỉ',
    'address_1'         => 'Địa chỉ 1',
    'address_2'         => 'Địa chỉ 2',
    'zipcode'           => 'Mã bưu điện',
    'phone'             => 'Điện thoại',
    'latitude'          => 'Vĩ độ',
    'longitude'         => 'Kinh độ',
    'all_warehouses'    => 'Tất cả kho hàng',

    // Stock
    'stock'             => 'Tồn kho',
    'warehouse_stocks'  => 'Tồn kho theo kho',
    'sku_code'          => 'Mã SKU',
    'quantity'          => 'Số lượng',
    'reserved'          => 'Đã đặt trước',
    'available'         => 'Có sẵn',
    'low_threshold'     => 'Ngưỡng tồn kho thấp',
    'adjust_stock'      => 'Điều chỉnh tồn kho',
    'add_stock'         => 'Thêm tồn kho',
    'adjust_quantity'   => 'Điều chỉnh số lượng',
    'stock_adjusted'    => 'Tồn kho đã được điều chỉnh thành công.',
    'adjust_hint'       => 'Số dương để thêm, số âm để giảm.',
    'import_stock'      => 'Nhập',
    'export_stock'      => 'Xuất',
    'download_template' => 'Tải mẫu',
    'import_success'    => 'Nhập hoàn tất: :success/:total thành công.',
    'import_file'       => 'Chọn tệp',
    'import_file_hint'  => 'Hỗ trợ tệp .xlsx, .csv. Cột: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Lịch sử biến động kho',
    'type'            => 'Loại',
    'reference'       => 'Tham chiếu',
    'note'            => 'Ghi chú',
    'all_types'       => 'Tất cả loại',

    // Stock Transfers
    'stock_transfers' => 'Chuyển kho',
    'transfer_number' => 'Mã chuyển kho',
    'from_warehouse'  => 'Từ kho',
    'to_warehouse'    => 'Đến kho',
    'create_transfer' => 'Tạo phiếu chuyển kho',
    'transfer_detail' => 'Chi tiết chuyển kho',
    'items'           => 'Mục',
    'received'        => 'Đã nhận',
    'ship'            => 'Giao hàng',
    'complete'        => 'Hoàn thành',
    'status'          => 'Trạng thái',

    // Shipment
    'packages'          => 'Kiện hàng',
    'package'           => 'Kiện hàng',
    'ship_package'      => 'Giao kiện hàng',
    'express_company'   => 'Đơn vị vận chuyển',
    'express_number'    => 'Mã vận đơn',
    'all_shipped'       => 'Tất cả kiện hàng đã được giao.',
    'partially_shipped' => 'Giao hàng một phần',

    // Allocation
    'allocation_strategy'   => 'Chiến lược phân bổ',
    'strategy_priority'     => 'Theo ưu tiên',
    'strategy_nearest'      => 'Kho gần nhất',
    'strategy_stock_first'  => 'Ưu tiên tồn kho',
    'strategy_cost_optimal' => 'Chi phí tối ưu',
    'allow_split_shipment'  => 'Cho phép tách đơn giao hàng',

    // Settings
    'warehouse_enabled'  => 'Bật quản lý kho hàng',
    'warehouse_settings' => 'Cài đặt kho hàng',
    'service_areas'      => 'Khu vực phục vụ',
    'service_area_hint'  => 'Xác định khu vực mà kho này phục vụ. Để trống cho phạm vi toàn cầu.',
    'all_states'         => 'Tất cả tỉnh/thành',
];
