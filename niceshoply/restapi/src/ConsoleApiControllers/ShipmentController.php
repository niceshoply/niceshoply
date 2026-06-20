<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\ShippingTraceService;
use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\RestAPI\Requests\ShipmentRequest;

class ShipmentController extends BaseController
{
    /**
     * @param  Order  $order
     * @param  ShipmentRequest  $request
     * @return mixed
     */
    public function store(Order $order, ShipmentRequest $request): mixed
    {
        try {
            $shipment = $order->shipments()->create($request->all());

            // 录入物流单号后，若订单处于可发货状态（非多仓模式），自动推进为「已发货」并通知客户。
            // 多仓模式下发货单在拣货阶段生成、状态另行流转，此处不自动推进。
            if (! system_setting('warehouse_enabled', false)) {
                $service      = StateMachineService::getInstance($order);
                $nextStatuses = collect($service->nextBackendStatuses())->pluck('status')->toArray();
                if (in_array(StateMachineService::SHIPPED, $nextStatuses)) {
                    $service->changeStatus(StateMachineService::SHIPPED, '', true);
                }
            }

            return create_json_success($shipment);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Order\Shipment  $shipment
     * @return mixed
     */
    public function destroy(Order\Shipment $shipment): mixed
    {
        try {
            $shipment->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Order\Shipment  $shipment
     * @return mixed
     */
    public function getTraces(Order\Shipment $shipment): mixed
    {
        try {
            $traces = ShippingTraceService::getInstance($shipment)->getTraces();

            return read_json_success($traces);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
