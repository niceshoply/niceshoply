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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Models\StockTransfer;
use NiceShoply\Common\Repositories\StockTransferRepo;
use NiceShoply\Common\Resources\StockTransferResource;
use NiceShoply\Common\Services\StockTransferService;

class StockTransferController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $transfers = StockTransferRepo::getInstance()
            ->builder($filters)
            ->with(['fromWarehouse', 'toWarehouse'])
            ->latest()
            ->paginate($perPage);

        return StockTransferResource::collection($transfers);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $data  = $request->only(['from_warehouse_id', 'to_warehouse_id', 'note']);
            $items = $request->input('items', []);

            $transfer = StockTransferService::getInstance()->createTransfer($data, $items);

            return create_json_success(new StockTransferResource($transfer->load(['fromWarehouse', 'toWarehouse', 'items'])));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return mixed
     */
    public function show(StockTransfer $stockTransfer): mixed
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items']);

        return read_json_success(new StockTransferResource($stockTransfer));
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return mixed
     */
    public function ship(StockTransfer $stockTransfer): mixed
    {
        try {
            StockTransferService::getInstance()->shipTransfer($stockTransfer);

            return update_json_success(new StockTransferResource($stockTransfer->fresh()));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return mixed
     */
    public function complete(StockTransfer $stockTransfer): mixed
    {
        try {
            StockTransferService::getInstance()->completeTransfer($stockTransfer);

            return update_json_success(new StockTransferResource($stockTransfer->fresh()));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return mixed
     */
    public function cancel(StockTransfer $stockTransfer): mixed
    {
        try {
            StockTransferService::getInstance()->cancelTransfer($stockTransfer);

            return update_json_success(new StockTransferResource($stockTransfer->fresh()));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
