<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiWarehouse\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\MultiWarehouse\Models\Warehouse;
use Plugin\MultiWarehouse\Models\WarehouseStock;
use Plugin\MultiWarehouse\Models\WarehouseTransfer;
use Plugin\MultiWarehouse\Services\WarehouseService;

class MultiWarehouseController extends BaseController
{
    protected string $modelClass = Warehouse::class;

    public function index(): mixed
    {
        $warehouses = Warehouse::query()->orderByDesc('is_default')->orderBy('id')->get();
        $transfers  = WarehouseTransfer::query()->orderByDesc('id')->limit(20)->get();

        return nice_view('MultiWarehouse::console.index', compact('warehouses', 'transfers'));
    }

    public function storeWarehouse(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'        => 'nullable|integer',
                'name'      => 'required|string|max:100',
                'code'      => 'required|string|max:32',
                'province'  => 'nullable|string|max:64',
                'city'      => 'nullable|string|max:64',
                'address'   => 'nullable|string|max:255',
                'is_default' => 'nullable|boolean',
                'is_active'  => 'nullable|boolean',
            ]);

            if ($request->boolean('is_default')) {
                Warehouse::query()->update(['is_default' => false]);
            }

            Warehouse::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'       => $data['name'],
                    'code'       => $data['code'],
                    'province'   => $data['province'] ?? '',
                    'city'       => $data['city'] ?? '',
                    'address'    => $data['address'] ?? '',
                    'is_default' => $request->boolean('is_default'),
                    'is_active'  => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('MultiWarehouse::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function stock(): mixed
    {
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('id')->get();
        $stocks = WarehouseStock::query()->with([])->orderByDesc('id')->limit(100)->get();
        $skuMap = Sku::query()->whereIn('id', $stocks->pluck('sku_id'))->pluck('code', 'id');

        return nice_view('MultiWarehouse::console.stock', compact('warehouses', 'stocks', 'skuMap'));
    }

    public function setStock(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'warehouse_id' => 'required|integer',
                'sku_id'       => 'required|integer',
                'quantity'     => 'required|integer|min:0',
            ]);
            WarehouseService::getInstance()->setStock((int) $data['warehouse_id'], (int) $data['sku_id'], (int) $data['quantity']);

            return json_success(__('MultiWarehouse::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function transfer(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'from_warehouse_id' => 'required|integer',
                'to_warehouse_id'   => 'required|integer',
                'sku_id'            => 'required|integer',
                'quantity'          => 'required|integer|min:1',
                'remark'            => 'nullable|string|max:255',
            ]);
            WarehouseService::getInstance()->transfer(
                (int) $data['from_warehouse_id'],
                (int) $data['to_warehouse_id'],
                (int) $data['sku_id'],
                (int) $data['quantity'],
                $data['remark'] ?? ''
            );

            return json_success(__('MultiWarehouse::common.transferred'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function syncAll(): mixed
    {
        $count = WarehouseService::getInstance()->syncAll();

        return json_success(__('MultiWarehouse::common.synced', ['count' => $count]));
    }
}
