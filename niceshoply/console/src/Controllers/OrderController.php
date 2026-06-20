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
use NiceShoply\Common\Jobs\ExportOrdersJob;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Rap2hpoutre\FastExcel\FastExcel;

class OrderController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => OrderRepo::getCriteria(),
            'orders'   => OrderRepo::getInstance()->list($filters),
            'stats'    => $this->indexStats(),
        ];

        return nice_view('console::orders.index', $data);
    }

    /**
     * 订单列表页顶部统计卡数据（按状态聚合，单次查询）。
     *
     * @return array<string, int>
     */
    protected function indexStats(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total'    => (int) $counts->sum(),
            'unpaid'   => (int) $counts->get(StateMachineService::UNPAID, 0),
            'paid'     => (int) $counts->get(StateMachineService::PAID, 0),
            'shipping' => (int) $counts->get(StateMachineService::SHIPPED, 0)
                + (int) $counts->get(StateMachineService::PARTIALLY_SHIPPED, 0),
            'cancelled' => (int) $counts->get(StateMachineService::CANCELLED, 0),
        ];
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function show(Order $order): mixed
    {
        $order->load(['items.options', 'fees', 'payments']);

        return $this->form($order);
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function edit(Order $order): mixed
    {
        $order->load(['items.options', 'fees', 'payments']);

        return $this->form($order);
    }

    /**
     * @param  Order  $order
     * @return mixed
     * @throws Exception
     */
    public function form(Order $order): mixed
    {
        $data = [
            'order'         => $order,
            'next_statuses' => StateMachineService::getInstance($order)->nextBackendStatuses(),
        ];

        return nice_view('console::orders.detail', $data);
    }

    /**
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function destroy(Order $order): RedirectResponse
    {
        try {
            OrderRepo::getInstance()->destroy($order);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Order  $order
     * @return mixed
     */
    public function printing(Order $order): mixed
    {
        $data = [
            'order' => $order,
        ];

        return nice_view('console::orders.printing', $data);
    }

    /**
     * @param  Request  $request
     * @param  Order  $order
     * @return mixed
     */
    public function changeStatus(Request $request, Order $order): mixed
    {
        $status   = $request->get('status');
        $comment  = $request->get('comment');
        $shipment = (array) $request->get('shipment', []);
        try {
            StateMachineService::getInstance($order)
                ->setShipment($shipment)
                ->changeStatus($status, $comment, true);

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Export batch orders
     *
     * @param  Request  $request
     * @return mixed
     */
    public function exportBatch(Request $request)
    {
        $filters    = $request->all();
        $orderCount = OrderRepo::getInstance()->builder($filters)->count();

        // For small datasets, export synchronously for instant download
        if ($orderCount <= 500) {
            $orders     = OrderRepo::getInstance()->builder($filters)->get();
            $exportData = collect();

            foreach ($orders as $order) {
                $exportData->push($this->formatOrderRow($order));
            }

            return (new FastExcel($exportData))->download('orders.xlsx');
        }

        // For large datasets, dispatch async job
        $filename = 'orders_'.date('Ymd_His').'.xlsx';
        $filePath = storage_path('app/exports/'.$filename);

        if (! is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        ExportOrdersJob::dispatch($filters, $filePath, app()->getLocale());

        return back()->with('success', console_trans('order.export_dispatched'));
    }

    /**
     * Download an exported file.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function downloadExport(Request $request)
    {
        $filename = basename($request->get('file', ''));
        $filePath = storage_path('app/exports/'.$filename);

        if (! $filename || ! file_exists($filePath)) {
            return back()->withErrors(['error' => console_trans('order.export_not_ready')]);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Format a single order row for export.
     */
    private function formatOrderRow(Order $order): array
    {
        return [
            console_trans('order.number')                 => $order->number,
            console_trans('order.created_at')             => $order->created_at,
            console_trans('order.customer_name')          => $order->customer_name ?? ($order->customer->name ?? ''),
            console_trans('order.status')                 => $order->status_format ?? $order->status,
            console_trans('order.total')                  => $order->total_format,
            console_trans('order.billing_method_name')    => $order->billing_method_name,
            console_trans('order.shipping_method_name')   => $order->shipping_method_name,
            console_trans('order.shipping_customer_name') => $order->shipping_customer_name,
            console_trans('order.shipping_telephone')     => $order->shipping_telephone,
            console_trans('order.shipping_address')       => $order->shipping_address_1.' '.$order->shipping_city.' '.$order->shipping_state.' '.$order->shipping_country,
            console_trans('order.comment')                => $order->comment,
        ];
    }
}
