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
use NiceShoply\Common\Models\Warehouse\StockMovement;
use NiceShoply\Common\Repositories\Warehouse\StockRepo;
use NiceShoply\Common\Resources\StockMovementResource;
use NiceShoply\Common\Resources\WarehouseStockResource;
use NiceShoply\Common\Services\WarehouseStockService;

class WarehouseStockController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $stocks = StockRepo::getInstance()->builder($filters)->paginate($perPage);

        return WarehouseStockResource::collection($stocks);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function adjust(Request $request): mixed
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $skuCode     = $request->input('sku_code');
            $quantity    = (int) $request->input('quantity');
            $note        = $request->input('note', '');

            WarehouseStockService::getInstance()->adjustStock($warehouseId, $skuCode, $quantity, $note);

            return update_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function movements(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $query = StockMovement::query()->with('warehouse')->latest();

        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($skuCode = $request->input('sku_code')) {
            $query->where('sku_code', 'like', "%{$skuCode}%");
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $movements = $query->paginate($perPage);

        return StockMovementResource::collection($movements);
    }
}
