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
    'warehouse'         => 'Armazém',
    'warehouses'        => 'Armazéns',
    'warehouse_code'    => 'Código do Armazém',
    'warehouse_name'    => 'Nome do Armazém',
    'code'              => 'Código',
    'name'              => 'Nome',
    'create_warehouse'  => 'Criar Armazém',
    'edit_warehouse'    => 'Editar Armazém',
    'default_warehouse' => 'Armazém Predefinido',
    'is_default'        => 'Predefinido',
    'priority'          => 'Prioridade',
    'active'            => 'Ativo',
    'description'       => 'Descrição',
    'contact_name'      => 'Nome do Contacto',
    'contact_phone'     => 'Telefone do Contacto',
    'country'           => 'País',
    'state'             => 'Distrito',
    'city'              => 'Cidade',
    'address'           => 'Morada',
    'address_1'         => 'Morada linha 1',
    'address_2'         => 'Morada linha 2',
    'zipcode'           => 'Código Postal',
    'phone'             => 'Telefone',
    'latitude'          => 'Latitude',
    'longitude'         => 'Longitude',
    'all_warehouses'    => 'Todos os Armazéns',

    // Stock
    'stock'             => 'Stock',
    'warehouse_stocks'  => 'Stocks do Armazém',
    'sku_code'          => 'Código SKU',
    'quantity'          => 'Quantidade',
    'reserved'          => 'Reservado',
    'available'         => 'Disponível',
    'low_threshold'     => 'Limiar de Stock Baixo',
    'adjust_stock'      => 'Ajustar Stock',
    'add_stock'         => 'Adicionar stock',
    'adjust_quantity'   => 'Ajustar Quantidade',
    'stock_adjusted'    => 'Stock ajustado com sucesso.',
    'adjust_hint'       => 'Número positivo para adicionar, negativo para subtrair.',
    'import_stock'      => 'Importar',
    'export_stock'      => 'Exportar',
    'download_template' => 'Descarregar modelo',
    'import_success'    => 'Importação concluída: :success/:total com sucesso.',
    'import_file'       => 'Selecionar ficheiro',
    'import_file_hint'  => 'Suporta ficheiros .xlsx, .csv. Colunas: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Movimentos de Stock',
    'type'            => 'Tipo',
    'reference'       => 'Referência',
    'note'            => 'Nota',
    'all_types'       => 'Todos os Tipos',

    // Stock Transfers
    'stock_transfers' => 'Transferências de Stock',
    'transfer_number' => 'Número de Transferência',
    'from_warehouse'  => 'Do Armazém',
    'to_warehouse'    => 'Para o Armazém',
    'create_transfer' => 'Criar Transferência',
    'transfer_detail' => 'Detalhe da Transferência',
    'items'           => 'Itens',
    'received'        => 'Recebido',
    'ship'            => 'Enviar',
    'complete'        => 'Concluir',
    'status'          => 'Estado',

    // Shipment
    'packages'          => 'Pacotes',
    'package'           => 'Pacote',
    'ship_package'      => 'Enviar Pacote',
    'express_company'   => 'Empresa de Transporte',
    'express_number'    => 'Número de Rastreio',
    'all_shipped'       => 'Todos os pacotes foram enviados.',
    'partially_shipped' => 'Parcialmente Enviado',

    // Allocation
    'allocation_strategy'   => 'Estratégia de Alocação',
    'strategy_priority'     => 'Baseado em Prioridade',
    'strategy_nearest'      => 'Armazém Mais Próximo',
    'strategy_stock_first'  => 'Stock Primeiro',
    'strategy_cost_optimal' => 'Custo Ótimo',
    'allow_split_shipment'  => 'Permitir Envio Dividido',

    // Settings
    'warehouse_enabled'  => 'Ativar Gestão de Armazéns',
    'warehouse_settings' => 'Definições de Armazém',
    'service_areas'      => 'Áreas de serviço',
    'service_area_hint'  => 'Defina as regiões servidas por este armazém. Deixe vazio para cobertura global.',
    'all_states'         => 'Todos os distritos',
];
