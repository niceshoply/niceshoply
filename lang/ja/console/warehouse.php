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
    'warehouses'        => '倉庫一覧',
    'warehouse_code'    => '倉庫コード',
    'warehouse_name'    => '倉庫名',
    'code'              => 'コード',
    'name'              => '名前',
    'create_warehouse'  => '倉庫を作成',
    'edit_warehouse'    => '倉庫を編集',
    'default_warehouse' => 'デフォルト倉庫',
    'is_default'        => 'デフォルト',
    'priority'          => '優先度',
    'active'            => '有効',
    'description'       => '説明',
    'contact_name'      => '担当者名',
    'contact_phone'     => '連絡先電話番号',
    'country'           => '国',
    'state'             => '都道府県',
    'city'              => '市区町村',
    'address'           => '住所',
    'address_1'         => '住所1',
    'address_2'         => '住所2',
    'zipcode'           => '郵便番号',
    'phone'             => '電話番号',
    'latitude'          => '緯度',
    'longitude'         => '経度',
    'all_warehouses'    => 'すべての倉庫',

    // Stock
    'stock'             => '在庫',
    'warehouse_stocks'  => '倉庫在庫',
    'sku_code'          => 'SKUコード',
    'quantity'          => '数量',
    'reserved'          => '予約済み',
    'available'         => '利用可能',
    'low_threshold'     => '在庫低下しきい値',
    'adjust_stock'      => '在庫を調整',
    'add_stock'         => '在庫追加',
    'adjust_quantity'   => '数量を調整',
    'stock_adjusted'    => '在庫が正常に調整されました。',
    'adjust_hint'       => '正の数で追加、負の数で減少。',
    'import_stock'      => 'インポート',
    'export_stock'      => 'エクスポート',
    'download_template' => 'テンプレートをダウンロード',
    'import_success'    => 'インポート完了：:success/:total 件成功。',
    'import_file'       => 'ファイルを選択',
    'import_file_hint'  => '.xlsx、.csv ファイルに対応。列：warehouse_id、sku_code、quantity。',

    // Stock Movements
    'stock_movements' => '在庫移動',
    'type'            => 'タイプ',
    'reference'       => '参照',
    'note'            => 'メモ',
    'all_types'       => 'すべてのタイプ',

    // Stock Transfers
    'stock_transfers' => '在庫移管',
    'transfer_number' => '移管番号',
    'from_warehouse'  => '出荷元倉庫',
    'to_warehouse'    => '出荷先倉庫',
    'create_transfer' => '移管を作成',
    'transfer_detail' => '移管詳細',
    'items'           => 'アイテム',
    'received'        => '受領済み',
    'ship'            => '出荷',
    'complete'        => '完了',
    'status'          => 'ステータス',

    // Shipment
    'packages'          => '荷物',
    'package'           => '荷物',
    'ship_package'      => '荷物を出荷',
    'express_company'   => '配送会社',
    'express_number'    => '追跡番号',
    'all_shipped'       => 'すべての荷物が出荷されました。',
    'partially_shipped' => '一部出荷済み',

    // Allocation
    'allocation_strategy'   => '割当戦略',
    'strategy_priority'     => '優先度ベース',
    'strategy_nearest'      => '最寄り倉庫',
    'strategy_stock_first'  => '在庫優先',
    'strategy_cost_optimal' => 'コスト最適',
    'allow_split_shipment'  => '分割出荷を許可',

    // Settings
    'warehouse_enabled'  => '倉庫管理を有効にする',
    'warehouse_settings' => '倉庫設定',
    'service_areas'      => 'サービスエリア',
    'service_area_hint'  => 'この倉庫がサービスする地域を定義します。空の場合はグローバル対応。',
    'all_states'         => 'すべての都道府県',
];
