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
    'warehouse'         => 'Almacén',
    'warehouses'        => 'Almacenes',
    'warehouse_code'    => 'Código de almacén',
    'warehouse_name'    => 'Nombre del almacén',
    'code'              => 'Código',
    'name'              => 'Nombre',
    'create_warehouse'  => 'Crear almacén',
    'edit_warehouse'    => 'Editar almacén',
    'default_warehouse' => 'Almacén predeterminado',
    'is_default'        => 'Predeterminado',
    'priority'          => 'Prioridad',
    'active'            => 'Activo',
    'description'       => 'Descripción',
    'contact_name'      => 'Nombre de contacto',
    'contact_phone'     => 'Teléfono de contacto',
    'country'           => 'País',
    'state'             => 'Estado/Provincia',
    'city'              => 'Ciudad',
    'address'           => 'Dirección',
    'address_1'         => 'Dirección línea 1',
    'address_2'         => 'Dirección línea 2',
    'zipcode'           => 'Código postal',
    'phone'             => 'Teléfono',
    'latitude'          => 'Latitud',
    'longitude'         => 'Longitud',
    'all_warehouses'    => 'Todos los almacenes',

    // Stock
    'stock'             => 'Stock',
    'warehouse_stocks'  => 'Stocks de almacén',
    'sku_code'          => 'Código SKU',
    'quantity'          => 'Cantidad',
    'reserved'          => 'Reservado',
    'available'         => 'Disponible',
    'low_threshold'     => 'Umbral de stock bajo',
    'adjust_stock'      => 'Ajustar stock',
    'add_stock'         => 'Agregar stock',
    'adjust_quantity'   => 'Ajustar cantidad',
    'stock_adjusted'    => 'Stock ajustado correctamente.',
    'adjust_hint'       => 'Número positivo para agregar, negativo para restar.',
    'import_stock'      => 'Importar',
    'export_stock'      => 'Exportar',
    'download_template' => 'Descargar plantilla',
    'import_success'    => 'Importación completada: :success/:total exitosos.',
    'import_file'       => 'Seleccionar archivo',
    'import_file_hint'  => 'Soporta archivos .xlsx, .csv. Columnas: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Movimientos de stock',
    'type'            => 'Tipo',
    'reference'       => 'Referencia',
    'note'            => 'Nota',
    'all_types'       => 'Todos los tipos',

    // Stock Transfers
    'stock_transfers' => 'Transferencias de stock',
    'transfer_number' => 'Número de transferencia',
    'from_warehouse'  => 'Almacén de origen',
    'to_warehouse'    => 'Almacén de destino',
    'create_transfer' => 'Crear transferencia',
    'transfer_detail' => 'Detalle de transferencia',
    'items'           => 'Artículos',
    'received'        => 'Recibido',
    'ship'            => 'Enviar',
    'complete'        => 'Completar',
    'status'          => 'Estado',

    // Shipment
    'packages'          => 'Paquetes',
    'package'           => 'Paquete',
    'ship_package'      => 'Enviar paquete',
    'express_company'   => 'Empresa de envío',
    'express_number'    => 'Número de seguimiento',
    'all_shipped'       => 'Todos los paquetes han sido enviados.',
    'partially_shipped' => 'Parcialmente enviado',

    // Allocation
    'allocation_strategy'   => 'Estrategia de asignación',
    'strategy_priority'     => 'Basada en prioridad',
    'strategy_nearest'      => 'Almacén más cercano',
    'strategy_stock_first'  => 'Stock primero',
    'strategy_cost_optimal' => 'Costo óptimo',
    'allow_split_shipment'  => 'Permitir envío dividido',

    // Settings
    'warehouse_enabled'  => 'Habilitar gestión de almacenes',
    'warehouse_settings' => 'Configuración de almacén',
    'service_areas'      => 'Áreas de servicio',
    'service_area_hint'  => 'Defina las regiones que atiende este almacén. Deje vacío para cobertura global.',
    'all_states'         => 'Todos los estados',
];
