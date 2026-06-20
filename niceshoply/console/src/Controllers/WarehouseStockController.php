<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Jobs\ImportStockJob;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Models\Warehouse\StockMovement;
use NiceShoply\Common\Repositories\Warehouse\StockRepo;
use NiceShoply\Common\Services\WarehouseStockService;
use Rap2hpoutre\FastExcel\FastExcel;

class WarehouseStockController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'warehouses' => Warehouse::query()->where('active', true)->orderBy('priority')->get(),
            'stocks'     => StockRepo::getInstance()->list($filters),
        ];

        return nice_view('console::warehouses.stock', $data);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function adjust(Request $request): RedirectResponse
    {
        try {
            $warehouseId = (int) $request->input('warehouse_id');
            $skuCode     = $request->input('sku_code');
            $quantity    = (int) $request->input('quantity');
            $note        = $request->input('note') ?? '';
            $adminId     = current_admin()->id ?? 0;

            WarehouseStockService::getInstance()->adjustStock($warehouseId, $skuCode, $quantity, $note, $adminId);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function movements(Request $request): mixed
    {
        $filters = $request->all();
        $builder = StockMovement::query()->with('warehouse')->orderByDesc('id');

        if ($warehouseId = $filters['warehouse_id'] ?? 0) {
            $builder->where('warehouse_id', $warehouseId);
        }
        if ($skuCode = $filters['sku_code'] ?? '') {
            $builder->where('sku_code', 'like', "%{$skuCode}%");
        }
        if ($type = $filters['type'] ?? '') {
            $builder->where('type', $type);
        }

        $data = [
            'warehouses' => Warehouse::query()->where('active', true)->get(),
            'movements'  => $builder->paginate(),
            'types'      => StockMovement::TYPES,
        ];

        return nice_view('console::warehouses.movements', $data);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function recentMovements(Request $request): JsonResponse
    {
        $warehouseId = (int) $request->input('warehouse_id');
        $skuCode     = $request->input('sku_code', '');

        $movements = StockMovement::query()
            ->where('warehouse_id', $warehouseId)
            ->where('sku_code', $skuCode)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'quantity', 'type', 'note', 'created_at']);

        return response()->json($movements);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $ids = array_filter(explode(',', $request->input('ids', '')));

        if ($ids) {
            $stocks = Stock::query()->with('warehouse')->whereIn('id', $ids)->get();
        } else {
            $stocks = StockRepo::getInstance()->builder($request->all())->get();
        }

        $exportData = $stocks->map(function ($item) {
            return [
                'warehouse_id'  => $item->warehouse_id,
                'warehouse'     => $item->warehouse->name ?? '',
                'sku_code'      => $item->sku_code,
                'quantity'      => $item->quantity,
                'reserved'      => $item->reserved_quantity,
                'available'     => $item->available_quantity,
                'low_threshold' => $item->low_stock_threshold,
            ];
        });

        return (new FastExcel($exportData))->download('warehouse_stocks.xlsx');
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            $adminId  = current_admin()->id ?? 0;
            $filePath = $request->file('file')->store('imports', 'local');
            $fullPath = storage_path('app/'.$filePath);

            ImportStockJob::dispatch($fullPath, $adminId);

            return back()->with('success', console_trans('warehouse.import_dispatched'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @return mixed
     */
    public function template(): mixed
    {
        $warehouses = Warehouse::query()->where('active', true)->orderBy('priority')->get();

        $data = collect();
        foreach ($warehouses as $wh) {
            $data->push([
                'warehouse_id' => $wh->id,
                'warehouse'    => $wh->name,
                'sku_code'     => '',
                'quantity'     => 0,
            ]);
        }

        if ($data->isEmpty()) {
            $data->push([
                'warehouse_id' => 1,
                'warehouse'    => 'Example Warehouse',
                'sku_code'     => 'SKU-001',
                'quantity'     => 100,
            ]);
        }

        return (new FastExcel($data))->download('warehouse_stocks_template.xlsx');
    }
}
