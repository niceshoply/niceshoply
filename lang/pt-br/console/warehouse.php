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
    'warehouse_code'    => 'Código do armazém',
    'warehouse_name'    => 'Nome do armazém',
    'code'              => 'Código',
    'name'              => 'Nome',
    'create_warehouse'  => 'Criar armazém',
    'edit_warehouse'    => 'Editar armazém',
    'default_warehouse' => 'Armazém padrão',
    'is_default'        => 'Padrão',
    'priority'          => 'Prioridade',
    'active'            => 'Ativo',
    'description'       => 'Descrição',
    'contact_name'      => 'Nome do contato',
    'contact_phone'     => 'Telefone do contato',
    'country'           => 'País',
    'state'             => 'Estado',
    'city'              => 'Cidade',
    'address'           => 'Endereço',
    'address_1'         => 'Endereço linha 1',
    'address_2'         => 'Endereço linha 2',
    'zipcode'           => 'CEP',
    'phone'             => 'Telefone',
    'latitude'          => 'Latitude',
    'longitude'         => 'Longitude',
    'all_warehouses'    => 'Todos os armazéns',

    // Stock
    'stock'             => 'Estoque',
    'warehouse_stocks'  => 'Estoques do armazém',
    'sku_code'          => 'Código SKU',
    'quantity'          => 'Quantidade',
    'reserved'          => 'Reservado',
    'available'         => 'Disponível',
    'low_threshold'     => 'Limite de estoque baixo',
    'adjust_stock'      => 'Ajustar estoque',
    'add_stock'         => 'Adicionar estoque',
    'adjust_quantity'   => 'Ajustar quantidade',
    'stock_adjusted'    => 'Estoque ajustado com sucesso.',
    'adjust_hint'       => 'Número positivo para adicionar, negativo para subtrair.',
    'import_stock'      => 'Importar',
    'export_stock'      => 'Exportar',
    'download_template' => 'Baixar modelo',
    'import_success'    => 'Importação concluída: :success/:total com sucesso.',
    'import_file'       => 'Selecionar arquivo',
    'import_file_hint'  => 'Suporta arquivos .xlsx, .csv. Colunas: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Movimentações de estoque',
    'type'            => 'Tipo',
    'reference'       => 'Referência',
    'note'            => 'Nota',
    'all_types'       => 'Todos os tipos',

    // Stock Transfers
    'stock_transfers' => 'Transferências de estoque',
    'transfer_number' => 'Número da transferência',
    'from_warehouse'  => 'Do armazém',
    'to_warehouse'    => 'Para armazém',
    'create_transfer' => 'Criar transferência',
    'transfer_detail' => 'Detalhe da transferência',
    'items'           => 'Itens',
    'received'        => 'Recebido',
    'ship'            => 'Enviar',
    'complete'        => 'Concluir',
    'status'          => 'Status',

    // Shipment
    'packages'          => 'Pacotes',
    'package'           => 'Pacote',
    'ship_package'      => 'Enviar pacote',
    'express_company'   => 'Transportadora',
    'express_number'    => 'Número de rastreamento',
    'all_shipped'       => 'Todos os pacotes foram enviados.',
    'partially_shipped' => 'Parcialmente enviado',

    // Allocation
    'allocation_strategy'   => 'Estratégia de alocação',
    'strategy_priority'     => 'Baseada em prioridade',
    'strategy_nearest'      => 'Armazém mais próximo',
    'strategy_stock_first'  => 'Estoque primeiro',
    'strategy_cost_optimal' => 'Custo ótimo',
    'allow_split_shipment'  => 'Permitir envio dividido',

    // Settings
    'warehouse_enabled'  => 'Habilitar gerenciamento de armazém',
    'warehouse_settings' => 'Configurações do armazém',
    'service_areas'      => 'Áreas de serviço',
    'service_area_hint'  => 'Defina as regiões atendidas por este armazém. Deixe vazio para cobertura global.',
    'all_states'         => 'Todos os estados',
];
