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
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Warehouse\Stock;

class WarehouseSyncStockCommand extends Command
{
    protected $signature = 'warehouse:sync-stock {--sku= : Sync a specific SKU code}';

    protected $description = 'Sync product_skus.quantity from warehouse_stocks totals';

    public function handle(): void
    {
        $skuCode = $this->option('sku');

        if ($skuCode) {
            $this->syncSku($skuCode);

            return;
        }

        $skuCodes = Stock::query()->distinct()->pluck('sku_code');
        $bar      = $this->output->createProgressBar($skuCodes->count());

        $this->info("Syncing {$skuCodes->count()} SKUs...");
        $bar->start();

        foreach ($skuCodes as $code) {
            $this->syncSku($code);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Stock sync completed.');
    }

    private function syncSku(string $skuCode): void
    {
        $total = (int) Stock::query()
            ->where('sku_code', $skuCode)
            ->whereHas('warehouse', fn ($q) => $q->where('active', true))
            ->selectRaw('SUM(quantity - reserved_quantity) as total')
            ->value('total');

        Sku::query()->where('code', $skuCode)->update(['quantity' => max(0, $total)]);
    }
}
