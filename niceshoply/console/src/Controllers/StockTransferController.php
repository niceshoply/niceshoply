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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\StockTransfer;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Repositories\StockTransferRepo;
use NiceShoply\Common\Services\StockTransferService;

class StockTransferController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria'  => StockTransferRepo::getCriteria(),
            'transfers' => StockTransferRepo::getInstance()->list($filters),
        ];

        return nice_view('console::stock_transfers.index', $data);
    }

    /**
     * @return mixed
     */
    public function create(): mixed
    {
        return $this->form(new StockTransfer);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $data             = $request->only(['from_warehouse_id', 'to_warehouse_id', 'note']);
            $items            = $request->input('items', []);
            $data['admin_id'] = current_admin()->id ?? 0;

            StockTransferService::getInstance()->createTransfer($data, $items);

            return redirect(console_route('stock_transfers.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('stock_transfers.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return mixed
     */
    public function show(StockTransfer $stockTransfer): mixed
    {
        return $this->form($stockTransfer->load('items', 'fromWarehouse', 'toWarehouse'));
    }

    /**
     * @param  $transfer
     * @return mixed
     */
    public function form($transfer): mixed
    {
        $data = [
            'transfer'   => $transfer,
            'warehouses' => Warehouse::query()->where('active', true)->orderBy('priority')->get(),
        ];

        return nice_view('console::stock_transfers.form', $data);
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return RedirectResponse
     */
    public function ship(StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            StockTransferService::getInstance()->shipTransfer($stockTransfer);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  StockTransfer  $stockTransfer
     * @return RedirectResponse
     */
    public function complete(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            $receivedQuantities = $request->input('received_quantities', []);
            StockTransferService::getInstance()->completeTransfer($stockTransfer, $receivedQuantities);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  StockTransfer  $stockTransfer
     * @return RedirectResponse
     */
    public function cancel(StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            StockTransferService::getInstance()->cancelTransfer($stockTransfer);

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
