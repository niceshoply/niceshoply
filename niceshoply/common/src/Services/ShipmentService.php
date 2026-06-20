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
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Shipment;

class ShipmentService extends BaseService
{
    /**
     * Ship a specific package (fill in logistics info and deduct stock).
     *
     * @param  Shipment  $shipment
     * @param  array  $logisticsData  ['express_code', 'express_company', 'express_number']
     * @return Shipment
     * @throws Exception
     */
    public function shipPackage(Shipment $shipment, array $logisticsData): Shipment
    {
        if ($shipment->status !== Shipment::STATUS_PENDING) {
            throw new Exception('Only pending shipments can be shipped.');
        }

        $stockService = WarehouseStockService::getInstance();

        return DB::transaction(function () use ($shipment, $logisticsData, $stockService) {
            $shipment->update([
                'express_code'    => $logisticsData['express_code'] ?? '',
                'express_company' => $logisticsData['express_company'] ?? '',
                'express_number'  => $logisticsData['express_number'] ?? '',
                'status'          => Shipment::STATUS_SHIPPED,
                'shipped_at'      => now(),
            ]);

            // Commit reserved stock (deduct actual quantity)
            foreach ($shipment->items as $item) {
                $stockService->commitReservedStock(
                    $shipment->warehouse_id, $item->sku_code, $item->quantity,
                    Order::class, $shipment->order_id
                );
            }

            fire_hook_action('service.shipment.shipped', ['shipment' => $shipment]);

            return $shipment->fresh();
        });
    }

    /**
     * Check if all shipments for an order have been shipped.
     *
     * @param  Order  $order
     * @return bool
     */
    public function checkAllShipped(Order $order): bool
    {
        $order->loadMissing('shipments');

        if ($order->shipments->isEmpty()) {
            return false;
        }

        return $order->shipments->every(fn ($s) => $s->status === Shipment::STATUS_SHIPPED || $s->status === Shipment::STATUS_DELIVERED);
    }

    /**
     * Check if any shipment has been shipped (for partially_shipped status).
     *
     * @param  Order  $order
     * @return bool
     */
    public function hasAnyShipped(Order $order): bool
    {
        $order->loadMissing('shipments');

        return $order->shipments->contains(fn ($s) => $s->status === Shipment::STATUS_SHIPPED || $s->status === Shipment::STATUS_DELIVERED);
    }
}
