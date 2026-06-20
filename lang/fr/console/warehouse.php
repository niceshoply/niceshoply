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
    'warehouse'         => 'Entrepôt',
    'warehouses'        => 'Entrepôts',
    'warehouse_code'    => 'Code entrepôt',
    'warehouse_name'    => 'Nom de l\'entrepôt',
    'code'              => 'Code',
    'name'              => 'Nom',
    'create_warehouse'  => 'Créer un entrepôt',
    'edit_warehouse'    => 'Modifier l\'entrepôt',
    'default_warehouse' => 'Entrepôt par défaut',
    'is_default'        => 'Par défaut',
    'priority'          => 'Priorité',
    'active'            => 'Actif',
    'description'       => 'Description',
    'contact_name'      => 'Nom du contact',
    'contact_phone'     => 'Téléphone du contact',
    'country'           => 'Pays',
    'state'             => 'Région',
    'city'              => 'Ville',
    'address'           => 'Adresse',
    'address_1'         => 'Adresse ligne 1',
    'address_2'         => 'Adresse ligne 2',
    'zipcode'           => 'Code postal',
    'phone'             => 'Téléphone',
    'latitude'          => 'Latitude',
    'longitude'         => 'Longitude',
    'all_warehouses'    => 'Tous les entrepôts',

    // Stock
    'stock'             => 'Stock',
    'warehouse_stocks'  => 'Stocks d\'entrepôt',
    'sku_code'          => 'Code SKU',
    'quantity'          => 'Quantité',
    'reserved'          => 'Réservé',
    'available'         => 'Disponible',
    'low_threshold'     => 'Seuil de stock bas',
    'adjust_stock'      => 'Ajuster le stock',
    'add_stock'         => 'Ajouter du stock',
    'adjust_quantity'   => 'Ajuster la quantité',
    'stock_adjusted'    => 'Stock ajusté avec succès.',
    'adjust_hint'       => 'Nombre positif pour ajouter, négatif pour soustraire.',
    'import_stock'      => 'Importer',
    'export_stock'      => 'Exporter',
    'download_template' => 'Télécharger le modèle',
    'import_success'    => 'Import terminé : :success/:total réussis.',
    'import_file'       => 'Sélectionner un fichier',
    'import_file_hint'  => 'Supporte les fichiers .xlsx, .csv. Colonnes : warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Mouvements de stock',
    'type'            => 'Type',
    'reference'       => 'Référence',
    'note'            => 'Note',
    'all_types'       => 'Tous les types',

    // Stock Transfers
    'stock_transfers' => 'Transferts de stock',
    'transfer_number' => 'Numéro de transfert',
    'from_warehouse'  => 'Entrepôt d\'origine',
    'to_warehouse'    => 'Entrepôt de destination',
    'create_transfer' => 'Créer un transfert',
    'transfer_detail' => 'Détail du transfert',
    'items'           => 'Articles',
    'received'        => 'Reçu',
    'ship'            => 'Expédier',
    'complete'        => 'Terminer',
    'status'          => 'Statut',

    // Shipment
    'packages'          => 'Colis',
    'package'           => 'Colis',
    'ship_package'      => 'Expédier le colis',
    'express_company'   => 'Transporteur',
    'express_number'    => 'Numéro de suivi',
    'all_shipped'       => 'Tous les colis ont été expédiés.',
    'partially_shipped' => 'Partiellement expédié',

    // Allocation
    'allocation_strategy'   => 'Stratégie d\'allocation',
    'strategy_priority'     => 'Basée sur la priorité',
    'strategy_nearest'      => 'Entrepôt le plus proche',
    'strategy_stock_first'  => 'Stock en priorité',
    'strategy_cost_optimal' => 'Coût optimal',
    'allow_split_shipment'  => 'Autoriser l\'expédition fractionnée',

    // Settings
    'warehouse_enabled'  => 'Activer la gestion des entrepôts',
    'warehouse_settings' => 'Paramètres d\'entrepôt',
    'service_areas'      => 'Zones de service',
    'service_area_hint'  => 'Définissez les régions desservies par cet entrepôt. Laissez vide pour une couverture mondiale.',
    'all_states'         => 'Toutes les provinces',
];
