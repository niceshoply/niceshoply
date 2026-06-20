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
    'warehouse'         => 'Gudang',
    'warehouses'        => 'Manajemen Gudang',
    'warehouse_code'    => 'Kode Gudang',
    'warehouse_name'    => 'Nama Gudang',
    'code'              => 'Kode',
    'name'              => 'Nama',
    'create_warehouse'  => 'Buat Gudang',
    'edit_warehouse'    => 'Edit Gudang',
    'default_warehouse' => 'Gudang Default',
    'is_default'        => 'Default',
    'priority'          => 'Prioritas',
    'active'            => 'Aktif',
    'description'       => 'Deskripsi',
    'contact_name'      => 'Nama Kontak',
    'contact_phone'     => 'Telepon Kontak',
    'country'           => 'Negara',
    'state'             => 'Provinsi',
    'city'              => 'Kota',
    'address'           => 'Alamat',
    'address_1'         => 'Alamat Baris 1',
    'address_2'         => 'Alamat Baris 2',
    'zipcode'           => 'Kode Pos',
    'phone'             => 'Telepon',
    'latitude'          => 'Garis Lintang',
    'longitude'         => 'Garis Bujur',
    'all_warehouses'    => 'Semua Gudang',

    // Stock
    'stock'             => 'Stok',
    'warehouse_stocks'  => 'Stok Gudang',
    'sku_code'          => 'Kode SKU',
    'quantity'          => 'Jumlah',
    'reserved'          => 'Dipesan',
    'available'         => 'Tersedia',
    'low_threshold'     => 'Batas Stok Rendah',
    'adjust_stock'      => 'Sesuaikan Stok',
    'add_stock'         => 'Tambah Stok',
    'adjust_quantity'   => 'Sesuaikan Jumlah',
    'stock_adjusted'    => 'Stok berhasil disesuaikan.',
    'adjust_hint'       => 'Angka positif untuk menambah, negatif untuk mengurangi.',
    'import_stock'      => 'Impor',
    'export_stock'      => 'Ekspor',
    'download_template' => 'Unduh Template',
    'import_success'    => 'Impor selesai: :success/:total berhasil.',
    'import_file'       => 'Pilih File',
    'import_file_hint'  => 'Mendukung file .xlsx, .csv. Kolom: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Pergerakan Stok',
    'type'            => 'Tipe',
    'reference'       => 'Referensi',
    'note'            => 'Catatan',
    'all_types'       => 'Semua Tipe',

    // Stock Transfers
    'stock_transfers' => 'Transfer Stok',
    'transfer_number' => 'Nomor Transfer',
    'from_warehouse'  => 'Dari Gudang',
    'to_warehouse'    => 'Ke Gudang',
    'create_transfer' => 'Buat Transfer',
    'transfer_detail' => 'Detail Transfer',
    'items'           => 'Item',
    'received'        => 'Diterima',
    'ship'            => 'Kirim',
    'complete'        => 'Selesai',
    'status'          => 'Status',

    // Shipment
    'packages'          => 'Paket',
    'package'           => 'Paket',
    'ship_package'      => 'Kirim Paket',
    'express_company'   => 'Perusahaan Ekspedisi',
    'express_number'    => 'Nomor Resi',
    'all_shipped'       => 'Semua paket telah dikirim.',
    'partially_shipped' => 'Terkirim Sebagian',

    // Allocation
    'allocation_strategy'   => 'Strategi Alokasi',
    'strategy_priority'     => 'Berdasarkan Prioritas',
    'strategy_nearest'      => 'Gudang Terdekat',
    'strategy_stock_first'  => 'Stok Terbanyak',
    'strategy_cost_optimal' => 'Biaya Optimal',
    'allow_split_shipment'  => 'Izinkan Pemisahan Pengiriman',

    // Settings
    'warehouse_enabled'  => 'Aktifkan Manajemen Gudang',
    'warehouse_settings' => 'Pengaturan Gudang',
    'service_areas'      => 'Area Layanan',
    'service_area_hint'  => 'Tentukan wilayah yang dilayani gudang ini. Kosongkan untuk cakupan global.',
    'all_states'         => 'Semua provinsi',
];
