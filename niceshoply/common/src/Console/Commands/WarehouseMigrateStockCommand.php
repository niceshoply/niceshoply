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
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Repositories\WarehouseRepo;

class WarehouseMigrateStockCommand extends Command
{
    protected $signature = 'warehouse:migrate-stock {--warehouse= : Target warehouse code (uses default if omitted)}';

    protected $description = 'Migrate existing product_skus.quantity into a warehouse as initial stock';

    public function handle(): void
    {
        $warehouseCode = $this->option('warehouse');

        if ($warehouseCode) {
            $warehouse = Warehouse::query()->where('code', $warehouseCode)->first();
        } else {
            $warehouse = WarehouseRepo::getInstance()->getDefaultWarehouse();
        }

        if (! $warehouse) {
            $this->error('No target warehouse found. Create a warehouse first or specify --warehouse=CODE.');

            return;
        }

        $this->info("Migrating stock to warehouse: {$warehouse->name} ({$warehouse->code})");

        $skus = Sku::query()->where('quantity', '>', 0)->get();
        $bar  = $this->output->createProgressBar($skus->count());

        $this->info("Found {$skus->count()} SKUs with stock to migrate.");
        $bar->start();

        $migrated = 0;
        foreach ($skus as $sku) {
            $existing = Stock::query()
                ->where('warehouse_id', $warehouse->id)
                ->where('sku_code', $sku->code)
                ->first();

            if ($existing) {
                $bar->advance();

                continue;
            }

            Stock::query()->create([
                'warehouse_id'      => $warehouse->id,
                'product_id'        => $sku->product_id,
                'sku_id'            => $sku->id,
                'sku_code'          => $sku->code,
                'quantity'          => $sku->quantity,
                'reserved_quantity' => 0,
            ]);

            $migrated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Migration completed. {$migrated} SKUs migrated to warehouse {$warehouse->code}.");
    }
}
