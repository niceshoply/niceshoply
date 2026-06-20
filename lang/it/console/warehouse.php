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
    'warehouse'         => 'Magazzino',
    'warehouses'        => 'Magazzini',
    'warehouse_code'    => 'Codice magazzino',
    'warehouse_name'    => 'Nome del magazzino',
    'code'              => 'Codice',
    'name'              => 'Nome',
    'create_warehouse'  => 'Crea magazzino',
    'edit_warehouse'    => 'Modifica magazzino',
    'default_warehouse' => 'Magazzino predefinito',
    'is_default'        => 'Predefinito',
    'priority'          => 'Priorità',
    'active'            => 'Attivo',
    'description'       => 'Descrizione',
    'contact_name'      => 'Nome contatto',
    'contact_phone'     => 'Telefono contatto',
    'country'           => 'Paese',
    'state'             => 'Regione',
    'city'              => 'Città',
    'address'           => 'Indirizzo',
    'address_1'         => 'Indirizzo riga 1',
    'address_2'         => 'Indirizzo riga 2',
    'zipcode'           => 'CAP',
    'phone'             => 'Telefono',
    'latitude'          => 'Latitudine',
    'longitude'         => 'Longitudine',
    'all_warehouses'    => 'Tutti i magazzini',

    // Stock
    'stock'             => 'Scorte',
    'warehouse_stocks'  => 'Scorte di magazzino',
    'sku_code'          => 'Codice SKU',
    'quantity'          => 'Quantità',
    'reserved'          => 'Riservato',
    'available'         => 'Disponibile',
    'low_threshold'     => 'Soglia scorte basse',
    'adjust_stock'      => 'Regola scorte',
    'add_stock'         => 'Aggiungi stock',
    'adjust_quantity'   => 'Regola quantità',
    'stock_adjusted'    => 'Scorte regolate con successo.',
    'adjust_hint'       => 'Numero positivo per aggiungere, negativo per sottrarre.',
    'import_stock'      => 'Importa',
    'export_stock'      => 'Esporta',
    'download_template' => 'Scarica modello',
    'import_success'    => 'Importazione completata: :success/:total riusciti.',
    'import_file'       => 'Seleziona file',
    'import_file_hint'  => 'Supporta file .xlsx, .csv. Colonne: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Movimenti di scorte',
    'type'            => 'Tipo',
    'reference'       => 'Riferimento',
    'note'            => 'Nota',
    'all_types'       => 'Tutti i tipi',

    // Stock Transfers
    'stock_transfers' => 'Trasferimenti di scorte',
    'transfer_number' => 'Numero di trasferimento',
    'from_warehouse'  => 'Dal magazzino',
    'to_warehouse'    => 'Al magazzino',
    'create_transfer' => 'Crea trasferimento',
    'transfer_detail' => 'Dettaglio trasferimento',
    'items'           => 'Articoli',
    'received'        => 'Ricevuto',
    'ship'            => 'Spedire',
    'complete'        => 'Completare',
    'status'          => 'Stato',

    // Shipment
    'packages'          => 'Pacchi',
    'package'           => 'Pacco',
    'ship_package'      => 'Spedisci pacco',
    'express_company'   => 'Corriere',
    'express_number'    => 'Numero di tracciamento',
    'all_shipped'       => 'Tutti i pacchi sono stati spediti.',
    'partially_shipped' => 'Parzialmente spedito',

    // Allocation
    'allocation_strategy'   => 'Strategia di allocazione',
    'strategy_priority'     => 'Basata sulla priorità',
    'strategy_nearest'      => 'Magazzino più vicino',
    'strategy_stock_first'  => 'Scorte prima',
    'strategy_cost_optimal' => 'Costo ottimale',
    'allow_split_shipment'  => 'Consenti spedizione frazionata',

    // Settings
    'warehouse_enabled'  => 'Abilita gestione magazzino',
    'warehouse_settings' => 'Impostazioni magazzino',
    'service_areas'      => 'Aree di servizio',
    'service_area_hint'  => 'Definisci le regioni servite da questo magazzino. Lascia vuoto per copertura globale.',
    'all_states'         => 'Tutte le province',
];
