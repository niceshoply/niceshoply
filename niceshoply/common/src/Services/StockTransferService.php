<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\StockTransfer;
use NiceShoply\Common\Repositories\StockTransferRepo;

class StockTransferService extends BaseService
{
    /**
     * Create a new stock transfer.
     *
     * @param  array  $data
     * @param  array  $items  [['sku_code' => '...', 'quantity' => N], ...]
     * @return StockTransfer
     * @throws Exception
     */
    public function createTransfer(array $data, array $items): StockTransfer
    {
        if (empty($items)) {
            throw new Exception('Transfer must have at least one item.');
        }

        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            throw new Exception('Source and destination warehouse cannot be the same.');
        }

        return DB::transaction(function () use ($data, $items) {
            $transfer = StockTransfer::query()->create([
                'number'            => StockTransferRepo::generateTransferNumber(),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id'   => $data['to_warehouse_id'],
                'status'            => StockTransfer::STATUS_PENDING,
                'note'              => $data['note'] ?? '',
                'admin_id'          => $data['admin_id'] ?? 0,
            ]);

            $transfer->items()->createMany($items);

            fire_hook_action('service.stock_transfer.created', ['transfer' => $transfer]);

            return $transfer->load('items');
        });
    }

    /**
     * Ship a transfer (pending → in_transit). Deducts stock from source warehouse.
     *
     * @param  StockTransfer  $transfer
     * @return StockTransfer
     * @throws Exception
     */
    public function shipTransfer(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== StockTransfer::STATUS_PENDING) {
            throw new Exception('Only pending transfers can be shipped.');
        }

        $stockService = WarehouseStockService::getInstance();

        return DB::transaction(function () use ($transfer, $stockService) {
            foreach ($transfer->items as $item) {
                $stockService->removeStock(
                    $transfer->from_warehouse_id, $item->sku_code, $item->quantity,
                    "Transfer out #{$transfer->number}", 0,
                    StockTransfer::class, $transfer->id
                );
            }

            $transfer->update([
                'status'     => StockTransfer::STATUS_IN_TRANSIT,
                'shipped_at' => now(),
            ]);

            fire_hook_action('service.stock_transfer.shipped', ['transfer' => $transfer]);

            return $transfer->fresh();
        });
    }

    /**
     * Complete a transfer (in_transit → completed). Adds stock to destination warehouse.
     *
     * @param  StockTransfer  $transfer
     * @param  array  $receivedQuantities  Optional: ['sku_code' => received_qty]
     * @return StockTransfer
     * @throws Exception
     */
    public function completeTransfer(StockTransfer $transfer, array $receivedQuantities = []): StockTransfer
    {
        if ($transfer->status !== StockTransfer::STATUS_IN_TRANSIT) {
            throw new Exception('Only in-transit transfers can be completed.');
        }

        $stockService = WarehouseStockService::getInstance();

        return DB::transaction(function () use ($transfer, $stockService, $receivedQuantities) {
            foreach ($transfer->items as $item) {
                $receivedQty = $receivedQuantities[$item->sku_code] ?? $item->quantity;
                $item->update(['received_quantity' => $receivedQty]);

                $stockService->addStock(
                    $transfer->to_warehouse_id, $item->sku_code, $receivedQty,
                    "Transfer in #{$transfer->number}", 0,
                    StockTransfer::class, $transfer->id
                );
            }

            $transfer->update([
                'status'       => StockTransfer::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            fire_hook_action('service.stock_transfer.completed', ['transfer' => $transfer]);

            return $transfer->fresh();
        });
    }

    /**
     * Cancel a transfer (only pending status).
     *
     * @param  StockTransfer  $transfer
     * @return StockTransfer
     * @throws Exception
     */
    public function cancelTransfer(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status !== StockTransfer::STATUS_PENDING) {
            throw new Exception('Only pending transfers can be cancelled.');
        }

        $transfer->update(['status' => StockTransfer::STATUS_CANCELLED]);

        return $transfer->fresh();
    }
}
