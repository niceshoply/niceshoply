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
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Models\Warehouse\StockMovement;
use NiceShoply\Common\Repositories\Warehouse\StockRepo;

class WarehouseStockService extends BaseService
{
    /**
     * Add stock to a warehouse (inbound).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity
     * @param  string  $note
     * @param  int  $adminId
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return Stock
     * @throws Exception
     */
    public function addStock(
        int $warehouseId, string $skuCode, int $quantity,
        string $note = '', int $adminId = 0,
        string $referenceType = '', int $referenceId = 0
    ): Stock {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive for addStock.');
        }

        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $note, $adminId, $referenceType, $referenceId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);
            $stock->increment('quantity', $quantity);

            $this->recordMovement($warehouseId, $skuCode, $quantity, StockMovement::TYPE_INBOUND, $referenceType, $referenceId, $note, $adminId);
            $this->syncSkuTotalStock($skuCode);

            fire_hook_action('warehouse.stock.added', ['stock' => $stock->fresh(), 'quantity' => $quantity]);

            return $stock->fresh();
        });
    }

    /**
     * Remove stock from a warehouse (outbound).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity
     * @param  string  $note
     * @param  int  $adminId
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return Stock
     * @throws Exception
     */
    public function removeStock(
        int $warehouseId, string $skuCode, int $quantity,
        string $note = '', int $adminId = 0,
        string $referenceType = '', int $referenceId = 0
    ): Stock {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive for removeStock.');
        }

        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $note, $adminId, $referenceType, $referenceId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);

            if ($stock->quantity < $quantity) {
                throw new Exception("Insufficient stock in warehouse {$warehouseId} for SKU {$skuCode}.");
            }

            $stock->decrement('quantity', $quantity);
            $this->recordMovement($warehouseId, $skuCode, -$quantity, StockMovement::TYPE_OUTBOUND, $referenceType, $referenceId, $note, $adminId);
            $this->syncSkuTotalStock($skuCode);

            fire_hook_action('warehouse.stock.removed', ['stock' => $stock->fresh(), 'quantity' => $quantity]);

            return $stock->fresh();
        });
    }

    /**
     * Reserve stock (for order placement).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return Stock
     * @throws Exception
     */
    public function reserveStock(int $warehouseId, string $skuCode, int $quantity, string $referenceType = '', int $referenceId = 0): Stock
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive for reserveStock.');
        }

        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $referenceType, $referenceId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);

            if ($stock->available_quantity < $quantity) {
                throw new Exception("Insufficient available stock in warehouse {$warehouseId} for SKU {$skuCode}.");
            }

            $stock->increment('reserved_quantity', $quantity);
            $this->recordMovement($warehouseId, $skuCode, -$quantity, StockMovement::TYPE_RESERVATION, $referenceType, $referenceId);

            return $stock->fresh();
        });
    }

    /**
     * Release reserved stock (for order cancellation).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return Stock
     * @throws Exception
     */
    public function releaseStock(int $warehouseId, string $skuCode, int $quantity, string $referenceType = '', int $referenceId = 0): Stock
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive for releaseStock.');
        }

        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $referenceType, $referenceId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);

            $releaseQty = min($quantity, $stock->reserved_quantity);
            if ($releaseQty > 0) {
                $stock->decrement('reserved_quantity', $releaseQty);
            }

            $this->recordMovement($warehouseId, $skuCode, $quantity, StockMovement::TYPE_RELEASE, $referenceType, $referenceId);

            return $stock->fresh();
        });
    }

    /**
     * Commit reserved stock (deduct actual quantity on shipment).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return Stock
     * @throws Exception
     */
    public function commitReservedStock(int $warehouseId, string $skuCode, int $quantity, string $referenceType = '', int $referenceId = 0): Stock
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be positive for commitReservedStock.');
        }

        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $referenceType, $referenceId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);

            $stock->decrement('quantity', $quantity);
            $stock->decrement('reserved_quantity', min($quantity, $stock->reserved_quantity));

            $this->recordMovement($warehouseId, $skuCode, -$quantity, StockMovement::TYPE_OUTBOUND, $referenceType, $referenceId, 'Committed from reservation');
            $this->syncSkuTotalStock($skuCode);

            return $stock->fresh();
        });
    }

    /**
     * Adjust stock manually (can be positive or negative).
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $quantity  Positive to add, negative to subtract
     * @param  string  $note
     * @param  int  $adminId
     * @return Stock
     * @throws Exception
     */
    public function adjustStock(int $warehouseId, string $skuCode, int $quantity, string $note = '', int $adminId = 0): Stock
    {
        return DB::transaction(function () use ($warehouseId, $skuCode, $quantity, $note, $adminId) {
            $stock = $this->getLockedStock($warehouseId, $skuCode);

            $newQuantity = $stock->quantity + $quantity;
            if ($newQuantity < 0) {
                throw new Exception("Adjustment would result in negative stock for SKU {$skuCode}.");
            }

            $stock->update(['quantity' => $newQuantity]);
            $this->recordMovement($warehouseId, $skuCode, $quantity, StockMovement::TYPE_ADJUSTMENT, '', 0, $note, $adminId);
            $this->syncSkuTotalStock($skuCode);

            fire_hook_action('warehouse.stock.adjusted', ['stock' => $stock->fresh(), 'quantity' => $quantity]);

            return $stock->fresh();
        });
    }

    /**
     * Sync product_skus.quantity from warehouse_stocks total.
     *
     * @param  string  $skuCode
     * @return void
     */
    public function syncSkuTotalStock(string $skuCode): void
    {
        $total = StockRepo::getInstance()->getTotalAvailable($skuCode);
        Sku::query()->where('code', $skuCode)->update(['quantity' => max(0, $total)]);
    }

    /**
     * Get a locked stock record for atomic operations.
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @return Stock
     */
    private function getLockedStock(int $warehouseId, string $skuCode): Stock
    {
        $stock = Stock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('sku_code', $skuCode)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            $sku   = Sku::query()->where('code', $skuCode)->first();
            $stock = Stock::query()->create([
                'warehouse_id'      => $warehouseId,
                'sku_code'          => $skuCode,
                'sku_id'            => $sku->id ?? 0,
                'product_id'        => $sku->product_id ?? 0,
                'quantity'          => 0,
                'reserved_quantity' => 0,
            ]);
            // Re-lock the newly created record
            $stock = Stock::query()->where('id', $stock->id)->lockForUpdate()->first();
        }

        return $stock;
    }

    /**
     * Record a stock movement.
     */
    private function recordMovement(
        int $warehouseId, string $skuCode, int $quantity, string $type,
        string $referenceType = '', int $referenceId = 0,
        string $note = '', int $adminId = 0
    ): void {
        StockMovement::query()->create([
            'warehouse_id'   => $warehouseId,
            'sku_code'       => $skuCode,
            'quantity'       => $quantity,
            'type'           => $type,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'note'           => $note,
            'admin_id'       => $adminId,
        ]);
    }
}
