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
    'warehouse'         => 'Depo',
    'warehouses'        => 'Depolar',
    'warehouse_code'    => 'Depo Kodu',
    'warehouse_name'    => 'Depo Adı',
    'code'              => 'Kod',
    'name'              => 'Ad',
    'create_warehouse'  => 'Depo Oluştur',
    'edit_warehouse'    => 'Depoyu Düzenle',
    'default_warehouse' => 'Varsayılan Depo',
    'is_default'        => 'Varsayılan',
    'priority'          => 'Öncelik',
    'active'            => 'Aktif',
    'description'       => 'Açıklama',
    'contact_name'      => 'İletişim Kişisi',
    'contact_phone'     => 'İletişim Telefonu',
    'country'           => 'Ülke',
    'state'             => 'İl',
    'city'              => 'İlçe',
    'address'           => 'Adres',
    'address_1'         => 'Adres Satırı 1',
    'address_2'         => 'Adres Satırı 2',
    'zipcode'           => 'Posta Kodu',
    'phone'             => 'Telefon',
    'latitude'          => 'Enlem',
    'longitude'         => 'Boylam',
    'all_warehouses'    => 'Tüm Depolar',

    // Stock
    'stock'             => 'Stok',
    'warehouse_stocks'  => 'Depo Stokları',
    'sku_code'          => 'SKU Kodu',
    'quantity'          => 'Miktar',
    'reserved'          => 'Rezerve',
    'available'         => 'Mevcut',
    'low_threshold'     => 'Düşük Stok Eşiği',
    'adjust_stock'      => 'Stok Ayarla',
    'add_stock'         => 'Stok Ekle',
    'adjust_quantity'   => 'Miktar Ayarla',
    'stock_adjusted'    => 'Stok başarıyla ayarlandı.',
    'adjust_hint'       => 'Artı sayı eklemek, eksi sayı çıkarmak için.',
    'import_stock'      => 'İçe Aktar',
    'export_stock'      => 'Dışa Aktar',
    'download_template' => 'Şablon İndir',
    'import_success'    => 'İçe aktarma tamamlandı: :success/:total başarılı.',
    'import_file'       => 'Dosya Seç',
    'import_file_hint'  => '.xlsx, .csv dosyalarını destekler. Sütunlar: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Stok Hareketleri',
    'type'            => 'Tür',
    'reference'       => 'Referans',
    'note'            => 'Not',
    'all_types'       => 'Tüm Türler',

    // Stock Transfers
    'stock_transfers' => 'Stok Transferleri',
    'transfer_number' => 'Transfer Numarası',
    'from_warehouse'  => 'Kaynak Depo',
    'to_warehouse'    => 'Hedef Depo',
    'create_transfer' => 'Transfer Oluştur',
    'transfer_detail' => 'Transfer Detayı',
    'items'           => 'Kalemler',
    'received'        => 'Teslim Alındı',
    'ship'            => 'Gönder',
    'complete'        => 'Tamamla',
    'status'          => 'Durum',

    // Shipment
    'packages'          => 'Paketler',
    'package'           => 'Paket',
    'ship_package'      => 'Paketi Gönder',
    'express_company'   => 'Kargo Şirketi',
    'express_number'    => 'Takip Numarası',
    'all_shipped'       => 'Tüm paketler gönderildi.',
    'partially_shipped' => 'Kısmen Gönderildi',

    // Allocation
    'allocation_strategy'   => 'Tahsis Stratejisi',
    'strategy_priority'     => 'Öncelik Bazlı',
    'strategy_nearest'      => 'En Yakın Depo',
    'strategy_stock_first'  => 'Stok Öncelikli',
    'strategy_cost_optimal' => 'Maliyet Optimali',
    'allow_split_shipment'  => 'Bölünmüş Gönderime İzin Ver',

    // Settings
    'warehouse_enabled'  => 'Depo Yönetimini Etkinleştir',
    'warehouse_settings' => 'Depo Ayarları',
    'service_areas'      => 'Hizmet Bölgeleri',
    'service_area_hint'  => 'Bu deponun hizmet verdiği bölgeleri tanımlayın. Küresel kapsam için boş bırakın.',
    'all_states'         => 'Tüm iller',
];
