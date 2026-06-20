<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Services\Notification\NotificationEventSubscriber;

class WarehouseStockWarningCommand extends Command
{
    protected $signature = 'warehouse:stock-warning';

    protected $description = 'Check warehouse stocks and send low stock warnings';

    public function handle(): void
    {
        $lowStocks = Stock::query()
            ->with(['warehouse'])
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->whereHas('warehouse', fn ($q) => $q->where('active', true))
            ->get();

        if ($lowStocks->isEmpty()) {
            $this->info('No low stock warnings.');

            return;
        }

        $this->warn("Found {$lowStocks->count()} low stock item(s):");

        $rows = [];
        foreach ($lowStocks as $stock) {
            $rows[] = [
                $stock->warehouse->name ?? '',
                $stock->sku_code,
                $stock->quantity,
                $stock->low_stock_threshold,
            ];
        }

        $this->table(['Warehouse', 'SKU', 'Quantity', 'Threshold'], $rows);

        fire_hook_action('warehouse.stock.warning', ['stocks' => $lowStocks]);

        // 业务事件接入统一通知：向已配置的外部渠道推送低库存预警
        NotificationEventSubscriber::notifyLowStock($lowStocks);

        $this->info('Stock warning check completed.');
    }
}
