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
    'warehouse'         => 'Magazijn',
    'warehouses'        => 'Magazijnen',
    'warehouse_code'    => 'Magazijncode',
    'warehouse_name'    => 'Magazijnnaam',
    'code'              => 'Code',
    'name'              => 'Naam',
    'create_warehouse'  => 'Magazijn aanmaken',
    'edit_warehouse'    => 'Magazijn bewerken',
    'default_warehouse' => 'Standaard magazijn',
    'is_default'        => 'Standaard',
    'priority'          => 'Prioriteit',
    'active'            => 'Actief',
    'description'       => 'Beschrijving',
    'contact_name'      => 'Contactpersoon',
    'contact_phone'     => 'Contacttelefoon',
    'country'           => 'Land',
    'state'             => 'Provincie',
    'city'              => 'Stad',
    'address'           => 'Adres',
    'address_1'         => 'Adresregel 1',
    'address_2'         => 'Adresregel 2',
    'zipcode'           => 'Postcode',
    'phone'             => 'Telefoon',
    'latitude'          => 'Breedtegraad',
    'longitude'         => 'Lengtegraad',
    'all_warehouses'    => 'Alle magazijnen',

    // Stock
    'stock'             => 'Voorraad',
    'warehouse_stocks'  => 'Magazijnvoorraden',
    'sku_code'          => 'SKU-code',
    'quantity'          => 'Aantal',
    'reserved'          => 'Gereserveerd',
    'available'         => 'Beschikbaar',
    'low_threshold'     => 'Lage voorraaddrempel',
    'adjust_stock'      => 'Voorraad aanpassen',
    'add_stock'         => 'Voorraad toevoegen',
    'adjust_quantity'   => 'Aantal aanpassen',
    'stock_adjusted'    => 'Voorraad succesvol aangepast.',
    'adjust_hint'       => 'Positief getal om toe te voegen, negatief om af te trekken.',
    'import_stock'      => 'Importeren',
    'export_stock'      => 'Exporteren',
    'download_template' => 'Sjabloon downloaden',
    'import_success'    => 'Import voltooid: :success/:total geslaagd.',
    'import_file'       => 'Bestand selecteren',
    'import_file_hint'  => 'Ondersteunt .xlsx, .csv bestanden. Kolommen: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Voorraadbewegingen',
    'type'            => 'Type',
    'reference'       => 'Referentie',
    'note'            => 'Notitie',
    'all_types'       => 'Alle typen',

    // Stock Transfers
    'stock_transfers' => 'Voorraadoverdrachten',
    'transfer_number' => 'Overdrachtnummer',
    'from_warehouse'  => 'Van magazijn',
    'to_warehouse'    => 'Naar magazijn',
    'create_transfer' => 'Overdracht aanmaken',
    'transfer_detail' => 'Overdrachtdetail',
    'items'           => 'Artikelen',
    'received'        => 'Ontvangen',
    'ship'            => 'Verzenden',
    'complete'        => 'Voltooien',
    'status'          => 'Status',

    // Shipment
    'packages'          => 'Pakketten',
    'package'           => 'Pakket',
    'ship_package'      => 'Pakket verzenden',
    'express_company'   => 'Verzendbedrijf',
    'express_number'    => 'Trackingnummer',
    'all_shipped'       => 'Alle pakketten zijn verzonden.',
    'partially_shipped' => 'Gedeeltelijk verzonden',

    // Allocation
    'allocation_strategy'   => 'Toewijzingsstrategie',
    'strategy_priority'     => 'Op basis van prioriteit',
    'strategy_nearest'      => 'Dichtstbijzijnde magazijn',
    'strategy_stock_first'  => 'Voorraad eerst',
    'strategy_cost_optimal' => 'Kostenoptimaal',
    'allow_split_shipment'  => 'Gesplitste verzending toestaan',

    // Settings
    'warehouse_enabled'  => 'Magazijnbeheer inschakelen',
    'warehouse_settings' => 'Magazijninstellingen',
    'service_areas'      => 'Servicegebieden',
    'service_area_hint'  => 'Definieer de regio\'s die dit magazijn bedient. Laat leeg voor wereldwijde dekking.',
    'all_states'         => 'Alle provincies',
];
