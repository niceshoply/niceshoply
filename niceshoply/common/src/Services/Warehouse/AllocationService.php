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
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\WarehouseStockService;

class AllocationService extends BaseService
{
    /**
     * Preview allocation without reserving stock.
     *
     * @param  array  $skuQuantities  [['sku_code' => '...', 'quantity' => N], ...]
     * @param  array  $destAddress
     * @param  string|null  $strategyName
     * @return AllocationResult
     * @throws Exception
     */
    public function preview(array $skuQuantities, array $destAddress = [], ?string $strategyName = null): AllocationResult
    {
        $strategy = AllocationStrategyFactory::create($strategyName);

        // Filter warehouses by service area
        $filtered           = WarehouseServiceAreaFilter::filter($destAddress);
        $matchedWarehouses  = $filtered['matched'];
        $fallbackWarehouses = $filtered['fallback'];

        // Try allocation with matched warehouses first
        $result = $strategy->allocate($skuQuantities, $destAddress, $matchedWarehouses);

        // If not fully allocated, try with all warehouses (matched + fallback)
        $usedFallback = false;
        if (! $result->isFullyAllocated() && $fallbackWarehouses->isNotEmpty()) {
            $allWarehouses = $matchedWarehouses->merge($fallbackWarehouses);
            $result        = $strategy->allocate($skuQuantities, $destAddress, $allWarehouses);
            $usedFallback  = true;
        }

        $result->usedFallback        = $usedFallback;
        $result->matchedWarehouseIds = $matchedWarehouses->pluck('id')->toArray();

        $data = ['result' => $result, 'sku_quantities' => $skuQuantities, 'dest_address' => $destAddress];
        fire_hook_action('service.warehouse.allocation.preview', $data);

        return $result;
    }

    /**
     * Allocate and reserve stock for an order.
     *
     * @param  Order  $order
     * @param  array  $skuQuantities
     * @param  array  $destAddress
     * @param  string|null  $strategyName
     * @return AllocationResult
     * @throws Exception
     */
    public function allocate(Order $order, array $skuQuantities, array $destAddress = [], ?string $strategyName = null): AllocationResult
    {
        $result = $this->preview($skuQuantities, $destAddress, $strategyName);

        if (! $result->isFullyAllocated()) {
            throw new Exception('Cannot fully allocate order. Insufficient stock for: '.implode(', ', $result->insufficientSkus));
        }

        $allowSplit = (bool) system_setting('warehouse_allow_split_shipment', true);
        if ($result->isSplit && ! $allowSplit) {
            throw new Exception('Split shipment is not allowed but order requires multiple warehouses.');
        }

        $stockService = WarehouseStockService::getInstance();

        DB::transaction(function () use ($result, $stockService, $order) {
            foreach ($result->allocations as $warehouseId => $items) {
                foreach ($items as $item) {
                    $stockService->reserveStock(
                        $warehouseId, $item['sku_code'], $item['quantity'],
                        Order::class, $order->id
                    );
                }
            }
        });

        fire_hook_action('service.warehouse.allocation.after', [
            'order' => $order, 'result' => $result,
        ]);

        return $result;
    }

    /**
     * Release all reserved stock for an order (on cancellation).
     *
     * @param  Order  $order
     * @param  AllocationResult|null  $result  If null, will look up from shipments
     * @return void
     */
    public function release(Order $order, ?AllocationResult $result = null): void
    {
        $stockService = WarehouseStockService::getInstance();

        if ($result) {
            DB::transaction(function () use ($result, $stockService, $order) {
                foreach ($result->allocations as $warehouseId => $items) {
                    foreach ($items as $item) {
                        $stockService->releaseStock(
                            $warehouseId, $item['sku_code'], $item['quantity'],
                            Order::class, $order->id
                        );
                    }
                }
            });

            return;
        }

        // Release from existing shipments
        $order->loadMissing('shipments.items');
        DB::transaction(function () use ($order, $stockService) {
            foreach ($order->shipments as $shipment) {
                if ($shipment->status !== 'shipped' && $shipment->status !== 'delivered') {
                    foreach ($shipment->items as $shipmentItem) {
                        $stockService->releaseStock(
                            $shipment->warehouse_id, $shipmentItem->sku_code, $shipmentItem->quantity,
                            Order::class, $order->id
                        );
                    }
                }
            }
        });
    }
}
