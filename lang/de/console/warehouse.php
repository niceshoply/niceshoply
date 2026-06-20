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
    'warehouse'         => 'Lager',
    'warehouses'        => 'Lager',
    'warehouse_code'    => 'Lagercode',
    'warehouse_name'    => 'Lagername',
    'code'              => 'Code',
    'name'              => 'Name',
    'create_warehouse'  => 'Lager erstellen',
    'edit_warehouse'    => 'Lager bearbeiten',
    'default_warehouse' => 'Standardlager',
    'is_default'        => 'Standard',
    'priority'          => 'Priorität',
    'active'            => 'Aktiv',
    'description'       => 'Beschreibung',
    'contact_name'      => 'Kontaktperson',
    'contact_phone'     => 'Kontakttelefon',
    'country'           => 'Land',
    'state'             => 'Bundesland',
    'city'              => 'Stadt',
    'address'           => 'Adresse',
    'address_1'         => 'Adresszeile 1',
    'address_2'         => 'Adresszeile 2',
    'zipcode'           => 'Postleitzahl',
    'phone'             => 'Telefon',
    'latitude'          => 'Breitengrad',
    'longitude'         => 'Längengrad',
    'all_warehouses'    => 'Alle Lager',

    // Stock
    'stock'             => 'Bestand',
    'warehouse_stocks'  => 'Lagerbestände',
    'sku_code'          => 'SKU-Code',
    'quantity'          => 'Menge',
    'reserved'          => 'Reserviert',
    'available'         => 'Verfügbar',
    'low_threshold'     => 'Niedriger Bestandsschwellenwert',
    'adjust_stock'      => 'Bestand anpassen',
    'add_stock'         => 'Bestand hinzufügen',
    'adjust_quantity'   => 'Menge anpassen',
    'stock_adjusted'    => 'Bestand erfolgreich angepasst.',
    'adjust_hint'       => 'Positive Zahl zum Hinzufügen, negative zum Abziehen.',
    'import_stock'      => 'Importieren',
    'export_stock'      => 'Exportieren',
    'download_template' => 'Vorlage herunterladen',
    'import_success'    => 'Import abgeschlossen: :success/:total erfolgreich.',
    'import_file'       => 'Datei auswählen',
    'import_file_hint'  => 'Unterstützt .xlsx, .csv Dateien. Spalten: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Bestandsbewegungen',
    'type'            => 'Typ',
    'reference'       => 'Referenz',
    'note'            => 'Notiz',
    'all_types'       => 'Alle Typen',

    // Stock Transfers
    'stock_transfers' => 'Bestandsübertragungen',
    'transfer_number' => 'Übertragungsnummer',
    'from_warehouse'  => 'Von Lager',
    'to_warehouse'    => 'An Lager',
    'create_transfer' => 'Übertragung erstellen',
    'transfer_detail' => 'Übertragungsdetail',
    'items'           => 'Artikel',
    'received'        => 'Empfangen',
    'ship'            => 'Versenden',
    'complete'        => 'Abschließen',
    'status'          => 'Status',

    // Shipment
    'packages'          => 'Pakete',
    'package'           => 'Paket',
    'ship_package'      => 'Paket versenden',
    'express_company'   => 'Versandunternehmen',
    'express_number'    => 'Sendungsnummer',
    'all_shipped'       => 'Alle Pakete wurden versendet.',
    'partially_shipped' => 'Teilweise versendet',

    // Allocation
    'allocation_strategy'   => 'Zuweisungsstrategie',
    'strategy_priority'     => 'Prioritätsbasiert',
    'strategy_nearest'      => 'Nächstgelegenes Lager',
    'strategy_stock_first'  => 'Bestand zuerst',
    'strategy_cost_optimal' => 'Kostenoptimal',
    'allow_split_shipment'  => 'Geteilten Versand erlauben',

    // Settings
    'warehouse_enabled'  => 'Lagerverwaltung aktivieren',
    'warehouse_settings' => 'Lagereinstellungen',
    'service_areas'      => 'Servicegebiete',
    'service_area_hint'  => 'Definieren Sie die Regionen, die dieses Lager bedient. Leer lassen für globale Abdeckung.',
    'all_states'         => 'Alle Bundesländer',
];
