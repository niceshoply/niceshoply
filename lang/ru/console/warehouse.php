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
    'warehouse'         => 'Склад',
    'warehouses'        => 'Склады',
    'warehouse_code'    => 'Код склада',
    'warehouse_name'    => 'Название склада',
    'code'              => 'Код',
    'name'              => 'Название',
    'create_warehouse'  => 'Создать склад',
    'edit_warehouse'    => 'Редактировать склад',
    'default_warehouse' => 'Склад по умолчанию',
    'is_default'        => 'По умолчанию',
    'priority'          => 'Приоритет',
    'active'            => 'Активный',
    'description'       => 'Описание',
    'contact_name'      => 'Контактное лицо',
    'contact_phone'     => 'Контактный телефон',
    'country'           => 'Страна',
    'state'             => 'Область',
    'city'              => 'Город',
    'address'           => 'Адрес',
    'address_1'         => 'Адрес строка 1',
    'address_2'         => 'Адрес строка 2',
    'zipcode'           => 'Почтовый индекс',
    'phone'             => 'Телефон',
    'latitude'          => 'Широта',
    'longitude'         => 'Долгота',
    'all_warehouses'    => 'Все склады',

    // Stock
    'stock'             => 'Запас',
    'warehouse_stocks'  => 'Запасы склада',
    'sku_code'          => 'Код SKU',
    'quantity'          => 'Количество',
    'reserved'          => 'Зарезервировано',
    'available'         => 'Доступно',
    'low_threshold'     => 'Порог низкого запаса',
    'adjust_stock'      => 'Корректировать запас',
    'add_stock'         => 'Добавить запас',
    'adjust_quantity'   => 'Корректировать количество',
    'stock_adjusted'    => 'Запас успешно скорректирован.',
    'adjust_hint'       => 'Положительное число для добавления, отрицательное для вычитания.',
    'import_stock'      => 'Импорт',
    'export_stock'      => 'Экспорт',
    'download_template' => 'Скачать шаблон',
    'import_success'    => 'Импорт завершён: :success/:total успешно.',
    'import_file'       => 'Выберите файл',
    'import_file_hint'  => 'Поддерживает файлы .xlsx, .csv. Столбцы: warehouse_id, sku_code, quantity.',

    // Stock Movements
    'stock_movements' => 'Движения запасов',
    'type'            => 'Тип',
    'reference'       => 'Ссылка',
    'note'            => 'Примечание',
    'all_types'       => 'Все типы',

    // Stock Transfers
    'stock_transfers' => 'Перемещения запасов',
    'transfer_number' => 'Номер перемещения',
    'from_warehouse'  => 'Со склада',
    'to_warehouse'    => 'На склад',
    'create_transfer' => 'Создать перемещение',
    'transfer_detail' => 'Детали перемещения',
    'items'           => 'Позиции',
    'received'        => 'Получено',
    'ship'            => 'Отправить',
    'complete'        => 'Завершить',
    'status'          => 'Статус',

    // Shipment
    'packages'          => 'Посылки',
    'package'           => 'Посылка',
    'ship_package'      => 'Отправить посылку',
    'express_company'   => 'Транспортная компания',
    'express_number'    => 'Номер отслеживания',
    'all_shipped'       => 'Все посылки отправлены.',
    'partially_shipped' => 'Частично отправлено',

    // Allocation
    'allocation_strategy'   => 'Стратегия распределения',
    'strategy_priority'     => 'По приоритету',
    'strategy_nearest'      => 'Ближайший склад',
    'strategy_stock_first'  => 'Сначала запас',
    'strategy_cost_optimal' => 'Оптимальная стоимость',
    'allow_split_shipment'  => 'Разрешить разделение отправки',

    // Settings
    'warehouse_enabled'  => 'Включить управление складами',
    'warehouse_settings' => 'Настройки склада',
    'service_areas'      => 'Зоны обслуживания',
    'service_area_hint'  => 'Определите регионы, которые обслуживает этот склад. Оставьте пустым для глобального покрытия.',
    'all_states'         => 'Все регионы',
];
