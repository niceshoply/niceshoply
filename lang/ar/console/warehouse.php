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
    'warehouse'         => 'المستودع',
    'warehouses'        => 'إدارة المستودعات',
    'warehouse_code'    => 'رمز المستودع',
    'warehouse_name'    => 'اسم المستودع',
    'code'              => 'الرمز',
    'name'              => 'الاسم',
    'create_warehouse'  => 'إنشاء مستودع',
    'edit_warehouse'    => 'تعديل المستودع',
    'default_warehouse' => 'المستودع الافتراضي',
    'is_default'        => 'افتراضي',
    'priority'          => 'الأولوية',
    'active'            => 'نشط',
    'description'       => 'الوصف',
    'contact_name'      => 'اسم جهة الاتصال',
    'contact_phone'     => 'هاتف جهة الاتصال',
    'country'           => 'الدولة',
    'state'             => 'المنطقة',
    'city'              => 'المدينة',
    'address'           => 'العنوان',
    'address_1'         => 'العنوان 1',
    'address_2'         => 'العنوان 2',
    'zipcode'           => 'الرمز البريدي',
    'phone'             => 'الهاتف',
    'latitude'          => 'خط العرض',
    'longitude'         => 'خط الطول',
    'all_warehouses'    => 'جميع المستودعات',

    // Stock
    'stock'             => 'المخزون',
    'warehouse_stocks'  => 'مخزون المستودع',
    'sku_code'          => 'رمز SKU',
    'quantity'          => 'الكمية',
    'reserved'          => 'محجوز',
    'available'         => 'متاح',
    'low_threshold'     => 'حد المخزون المنخفض',
    'adjust_stock'      => 'تعديل المخزون',
    'add_stock'         => 'إضافة مخزون',
    'adjust_quantity'   => 'تعديل الكمية',
    'stock_adjusted'    => 'تم تعديل المخزون بنجاح.',
    'adjust_hint'       => 'رقم موجب للإضافة، سالب للخصم.',
    'import_stock'      => 'استيراد',
    'export_stock'      => 'تصدير',
    'download_template' => 'تحميل القالب',
    'import_success'    => 'اكتمل الاستيراد: :success/:total بنجاح.',
    'import_file'       => 'اختر ملف',
    'import_file_hint'  => 'يدعم ملفات .xlsx, .csv. الأعمدة: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'حركات المخزون',
    'type'            => 'النوع',
    'reference'       => 'المرجع',
    'note'            => 'ملاحظة',
    'all_types'       => 'جميع الأنواع',

    // Stock Transfers
    'stock_transfers' => 'تحويلات المخزون',
    'transfer_number' => 'رقم التحويل',
    'from_warehouse'  => 'من المستودع',
    'to_warehouse'    => 'إلى المستودع',
    'create_transfer' => 'إنشاء تحويل',
    'transfer_detail' => 'تفاصيل التحويل',
    'items'           => 'العناصر',
    'received'        => 'مستلم',
    'ship'            => 'شحن',
    'complete'        => 'مكتمل',
    'status'          => 'الحالة',

    // Shipment
    'packages'          => 'الطرود',
    'package'           => 'طرد',
    'ship_package'      => 'شحن الطرد',
    'express_company'   => 'شركة الشحن',
    'express_number'    => 'رقم التتبع',
    'all_shipped'       => 'تم شحن جميع الطرود.',
    'partially_shipped' => 'شحن جزئي',

    // Allocation
    'allocation_strategy'   => 'استراتيجية التوزيع',
    'strategy_priority'     => 'حسب الأولوية',
    'strategy_nearest'      => 'أقرب مستودع',
    'strategy_stock_first'  => 'المخزون أولاً',
    'strategy_cost_optimal' => 'التكلفة المثلى',
    'allow_split_shipment'  => 'السماح بتقسيم الشحنة',

    // Settings
    'warehouse_enabled'  => 'تفعيل إدارة المستودعات',
    'warehouse_settings' => 'إعدادات المستودع',
    'service_areas'      => 'مناطق الخدمة',
    'service_area_hint'  => 'حدد المناطق التي يخدمها هذا المستودع. اتركه فارغاً للتغطية العالمية.',
    'all_states'         => 'جميع المحافظات',
];
