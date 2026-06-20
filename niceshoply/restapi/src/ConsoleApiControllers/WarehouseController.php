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
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Repositories\WarehouseRepo;
use NiceShoply\Common\Resources\WarehouseResource;

class WarehouseController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $warehouses = WarehouseRepo::getInstance()->builder($filters)->paginate($perPage);

        return WarehouseResource::collection($warehouses);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $data = $request->only([
                'name', 'code', 'country_id', 'country', 'state_id', 'state',
                'city', 'address_1', 'address_2', 'zipcode', 'phone',
                'latitude', 'longitude', 'priority', 'is_default', 'active',
            ]);
            $warehouse = WarehouseRepo::getInstance()->create($data);

            return create_json_success(new WarehouseResource($warehouse));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Warehouse  $warehouse
     * @return mixed
     */
    public function show(Warehouse $warehouse): mixed
    {
        return read_json_success(new WarehouseResource($warehouse));
    }

    /**
     * @param  Warehouse  $warehouse
     * @param  Request  $request
     * @return mixed
     */
    public function update(Warehouse $warehouse, Request $request): mixed
    {
        try {
            $data = $request->only([
                'name', 'code', 'country_id', 'country', 'state_id', 'state',
                'city', 'address_1', 'address_2', 'zipcode', 'phone',
                'latitude', 'longitude', 'priority', 'is_default', 'active',
            ]);
            WarehouseRepo::getInstance()->update($warehouse, $data);

            return update_json_success(new WarehouseResource($warehouse->fresh()));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Warehouse  $warehouse
     * @return mixed
     */
    public function destroy(Warehouse $warehouse): mixed
    {
        try {
            $warehouse->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function active(): AnonymousResourceCollection
    {
        $warehouses = WarehouseRepo::getInstance()->getActiveWarehouses();

        return WarehouseResource::collection($warehouses);
    }
}
