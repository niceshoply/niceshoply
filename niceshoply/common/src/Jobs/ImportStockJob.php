<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Services\WarehouseStockService;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        private readonly string $filePath,
        private readonly int $adminId
    ) {}

    public function handle(): void
    {
        $success      = 0;
        $errors       = [];
        $rows         = (new FastExcel)->import($this->filePath);
        $warehouseIds = Warehouse::query()->pluck('id')->toArray();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $values = array_values($row);

            $warehouseId = (int) ($row['warehouse_id'] ?? $values[0] ?? 0);
            $skuCode     = trim($row['sku_code'] ?? $values[2] ?? '');
            $quantity    = (int) ($row['quantity'] ?? $values[3] ?? 0);

            if (! $warehouseId) {
                foreach ($row as $key => $val) {
                    if (strtolower($key) === 'warehouse_id') {
                        $warehouseId = (int) $val;
                        break;
                    }
                }
            }

            if (! $skuCode || $quantity < 0) {
                $errors[] = "Row {$rowNum}: invalid data (sku_code=".($skuCode ?: 'empty').", quantity={$quantity})";

                continue;
            }

            if (! in_array($warehouseId, $warehouseIds)) {
                $errors[] = "Row {$rowNum}: warehouse {$warehouseId} not found";

                continue;
            }

            try {
                $stock      = Stock::query()->where('warehouse_id', $warehouseId)->where('sku_code', $skuCode)->first();
                $currentQty = $stock->quantity ?? 0;
                $delta      = $quantity - $currentQty;

                if ($delta == 0) {
                    $success++;

                    continue;
                }

                WarehouseStockService::getInstance()->adjustStock($warehouseId, $skuCode, $delta, 'Import', $this->adminId);
                $success++;
            } catch (Exception $e) {
                $errors[] = "Row {$rowNum}: {$e->getMessage()}";
            }
        }

        Log::info("Stock import completed: {$success}/".count($rows).' rows succeeded', [
            'errors' => array_slice($errors, 0, 10),
        ]);

        // Clean up temp file
        if (file_exists($this->filePath)) {
            @unlink($this->filePath);
        }
    }
}
