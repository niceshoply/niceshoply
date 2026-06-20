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
    'warehouse'         => 'คลังสินค้า',
    'warehouses'        => 'คลังสินค้าทั้งหมด',
    'warehouse_code'    => 'รหัสคลังสินค้า',
    'warehouse_name'    => 'ชื่อคลังสินค้า',
    'code'              => 'รหัส',
    'name'              => 'ชื่อ',
    'create_warehouse'  => 'สร้างคลังสินค้า',
    'edit_warehouse'    => 'แก้ไขคลังสินค้า',
    'default_warehouse' => 'คลังสินค้าเริ่มต้น',
    'is_default'        => 'ค่าเริ่มต้น',
    'priority'          => 'ลำดับความสำคัญ',
    'active'            => 'ใช้งาน',
    'description'       => 'คำอธิบาย',
    'contact_name'      => 'ชื่อผู้ติดต่อ',
    'contact_phone'     => 'เบอร์ติดต่อ',
    'country'           => 'ประเทศ',
    'state'             => 'จังหวัด',
    'city'              => 'เมือง',
    'address'           => 'ที่อยู่',
    'address_1'         => 'ที่อยู่ 1',
    'address_2'         => 'ที่อยู่ 2',
    'zipcode'           => 'รหัสไปรษณีย์',
    'phone'             => 'โทรศัพท์',
    'latitude'          => 'ละติจูด',
    'longitude'         => 'ลองจิจูด',
    'all_warehouses'    => 'คลังสินค้าทั้งหมด',

    // Stock
    'stock'             => 'สต็อก',
    'warehouse_stocks'  => 'สต็อกคลังสินค้า',
    'sku_code'          => 'รหัส SKU',
    'quantity'          => 'จำนวน',
    'reserved'          => 'จองแล้ว',
    'available'         => 'พร้อมใช้งาน',
    'low_threshold'     => 'เกณฑ์สต็อกต่ำ',
    'adjust_stock'      => 'ปรับสต็อก',
    'add_stock'         => 'เพิ่มสต็อก',
    'adjust_quantity'   => 'ปรับจำนวน',
    'stock_adjusted'    => 'ปรับสต็อกเรียบร้อยแล้ว',
    'adjust_hint'       => 'จำนวนบวกเพื่อเพิ่ม จำนวนลบเพื่อลด',
    'import_stock'      => 'นำเข้า',
    'export_stock'      => 'ส่งออก',
    'download_template' => 'ดาวน์โหลดเทมเพลต',
    'import_success'    => 'นำเข้าเสร็จสิ้น: :success/:total สำเร็จ',
    'import_file'       => 'เลือกไฟล์',
    'import_file_hint'  => 'รองรับไฟล์ .xlsx, .csv คอลัมน์: warehouse_id, sku_code, quantity',

    // Stock Movements
    'stock_movements' => 'การเคลื่อนไหวสต็อก',
    'type'            => 'ประเภท',
    'reference'       => 'อ้างอิง',
    'note'            => 'หมายเหตุ',
    'all_types'       => 'ทุกประเภท',

    // Stock Transfers
    'stock_transfers' => 'การโอนสต็อก',
    'transfer_number' => 'หมายเลขการโอน',
    'from_warehouse'  => 'จากคลังสินค้า',
    'to_warehouse'    => 'ไปยังคลังสินค้า',
    'create_transfer' => 'สร้างการโอน',
    'transfer_detail' => 'รายละเอียดการโอน',
    'items'           => 'รายการ',
    'received'        => 'รับแล้ว',
    'ship'            => 'จัดส่ง',
    'complete'        => 'เสร็จสิ้น',
    'status'          => 'สถานะ',

    // Shipment
    'packages'          => 'พัสดุ',
    'package'           => 'พัสดุ',
    'ship_package'      => 'จัดส่งพัสดุ',
    'express_company'   => 'บริษัทขนส่ง',
    'express_number'    => 'หมายเลขติดตาม',
    'all_shipped'       => 'พัสดุทั้งหมดถูกจัดส่งแล้ว',
    'partially_shipped' => 'จัดส่งบางส่วน',

    // Allocation
    'allocation_strategy'   => 'กลยุทธ์การจัดสรร',
    'strategy_priority'     => 'ตามลำดับความสำคัญ',
    'strategy_nearest'      => 'คลังสินค้าที่ใกล้ที่สุด',
    'strategy_stock_first'  => 'สต็อกมาก่อน',
    'strategy_cost_optimal' => 'ต้นทุนที่เหมาะสม',
    'allow_split_shipment'  => 'อนุญาตการแยกจัดส่ง',

    // Settings
    'warehouse_enabled'  => 'เปิดใช้งานการจัดการคลังสินค้า',
    'warehouse_settings' => 'ตั้งค่าคลังสินค้า',
    'service_areas'      => 'พื้นที่ให้บริการ',
    'service_area_hint'  => 'กำหนดพื้นที่ที่คลังสินค้านี้ให้บริการ เว้นว่างสำหรับครอบคลุมทั่วโลก',
    'all_states'         => 'ทุกจังหวัด',
];
