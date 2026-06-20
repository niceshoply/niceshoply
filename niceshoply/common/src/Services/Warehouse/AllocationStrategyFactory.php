<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Warehouse;

use Exception;
use NiceShoply\Common\Services\Warehouse\Strategies\CostOptimalStrategy;
use NiceShoply\Common\Services\Warehouse\Strategies\NearestStrategy;
use NiceShoply\Common\Services\Warehouse\Strategies\PriorityStrategy;
use NiceShoply\Common\Services\Warehouse\Strategies\StockFirstStrategy;

class AllocationStrategyFactory
{
    private static array $customStrategies = [];

    /**
     * Create a strategy instance based on the configured strategy name.
     *
     * @param  string|null  $strategyName
     * @return AllocationStrategyInterface
     * @throws Exception
     */
    public static function create(?string $strategyName = null): AllocationStrategyInterface
    {
        $strategyName = $strategyName ?: system_setting('warehouse_allocation_strategy', 'priority');

        $strategies = array_merge([
            'priority'     => PriorityStrategy::class,
            'nearest'      => NearestStrategy::class,
            'stock_first'  => StockFirstStrategy::class,
            'cost_optimal' => CostOptimalStrategy::class,
        ], self::$customStrategies);

        $strategies = fire_hook_filter('service.warehouse.allocation_strategies', $strategies);

        if (! isset($strategies[$strategyName])) {
            throw new Exception("Unknown allocation strategy: {$strategyName}");
        }

        return new $strategies[$strategyName];
    }

    /**
     * Register a custom allocation strategy.
     *
     * @param  string  $name
     * @param  string  $className
     * @return void
     */
    public static function register(string $name, string $className): void
    {
        self::$customStrategies[$name] = $className;
    }
}
