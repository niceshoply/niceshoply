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
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\OrderReturn;
use NiceShoply\Common\Models\OrderReturn\Payment;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Repositories\CatalogRepo;
use NiceShoply\Common\Repositories\OrderReturnRepo;
use NiceShoply\Common\Resources\CatalogSimple;
use NiceShoply\Common\Services\Refund\RefundService;
use NiceShoply\Common\Services\ReturnStateService;
use Rap2hpoutre\FastExcel\FastExcel;
use Throwable;

class OrderReturnController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters      = $request->all();
        $repo         = OrderReturnRepo::getInstance();
        $orderReturns = $repo->list($filters);
        $orderReturns->load(['customer', 'product', 'reason']);

        $allStatuses = array_map(static fn ($status) => [
            'status' => $status,
            'name'   => trans("common/rma.$status"),
        ], ReturnStateService::ORDER_STATUS);

        $data = [
            'criteria'       => OrderReturnRepo::getCriteria(),
            'order_returns'  => $orderReturns,
            'status_counts'  => $repo->statusCounts($filters),
            'all_statuses'   => $allStatuses,
            'current_status' => $filters['status'] ?? '',
        ];

        return nice_view('console::order_returns.index', $data);
    }

    /**
     * OrderReturn creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new OrderReturn);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $data        = $request->all();
            $orderReturn = OrderReturnRepo::getInstance()->create($data);

            return redirect(console_route('order_returns.index'))
                ->with('instance', $orderReturn)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  OrderReturn  $order_return
     * @return mixed
     * @throws Exception
     */
    public function edit(OrderReturn $order_return): mixed
    {
        return $this->form($order_return);
    }

    /**
     * @param  $orderReturn
     * @return mixed
     * @throws Exception
     */
    public function form($orderReturn): mixed
    {
        if ($orderReturn->exists) {
            $orderReturn->load(['customer', 'product', 'reason', 'order', 'orderItem', 'histories', 'payments', 'refunds']);
        }

        $catalogs = CatalogSimple::collection(CatalogRepo::getInstance()->all(['active' => 1]))->jsonSerialize();
        $data     = [
            'next_statuses' => ReturnStateService::getInstance($orderReturn)->nextBackendStatuses(),
            'order_return'  => $orderReturn,
            'catalogs'      => $catalogs,
        ];

        return nice_view('console::order_returns.form', $data);
    }

    /**
     * @param  Request  $request
     * @param  OrderReturn  $orderReturn
     * @return RedirectResponse
     */
    public function update(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        try {
            $data        = $request->all();
            $orderReturn = OrderReturnRepo::getInstance()->update($orderReturn, $data);

            return redirect(console_route('order_returns.index'))
                ->with('instance', $orderReturn)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  OrderReturn  $order_return
     * @return RedirectResponse
     */
    public function destroy(OrderReturn $order_return): RedirectResponse
    {
        try {
            OrderReturnRepo::getInstance()->destroy($order_return);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  OrderReturn  $orderReturn
     * @return mixed
     */
    public function changeStatus(Request $request, OrderReturn $orderReturn): mixed
    {
        $status  = $request->get('status');
        $comment = $request->get('comment');
        try {
            ReturnStateService::getInstance($orderReturn)->changeStatus($status, $comment, true);

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 发起退款：创建退款单并执行（余额/人工/原路）。
     *
     * @param  Request  $request
     * @param  OrderReturn  $orderReturn
     * @return mixed
     */
    public function refund(Request $request, OrderReturn $orderReturn): mixed
    {
        $amount  = (float) $request->get('amount', 0);
        $type    = (string) $request->get('type', Payment::TYPE_WALLET);
        $comment = (string) $request->get('comment', '');

        if ($amount <= 0) {
            return json_fail(console_trans('order_return.refund_amount_invalid'));
        }

        $methodMap = [
            Payment::TYPE_WALLET   => Refund::METHOD_BALANCE,
            Payment::TYPE_ORIGINAL => Refund::METHOD_ORIGINAL,
            'manual'               => Refund::METHOD_MANUAL,
        ];
        $method = $methodMap[$type] ?? null;
        if (! $method) {
            return json_fail(console_trans('common.invalid_parameters'));
        }
        if ($method === Refund::METHOD_BALANCE && ! $orderReturn->customer_id) {
            return json_fail(console_trans('order_return.refund_no_customer'));
        }

        DB::beginTransaction();
        try {
            $refund = RefundService::getInstance()->create([
                'order_id'        => $orderReturn->order_id,
                'order_return_id' => $orderReturn->id,
                'amount'          => $amount,
                'method'          => $method,
                'reason'          => $comment ?: console_trans('order_return.refund_transaction_comment', ['number' => $orderReturn->number]),
                'operator_id'     => (int) (current_admin()->id ?? 0),
            ]);

            $refund = RefundService::getInstance()->process($refund, (int) (current_admin()->id ?? 0));

            if ($refund->status !== 'succeeded') {
                DB::rollBack();
                $lastLog = $refund->logs()->latest('id')->first();

                return json_fail($lastLog->comment ?? console_trans('common.operation_failed'));
            }

            // 兼容旧版退货退款记录展示
            Payment::query()->create([
                'order_return_id' => $orderReturn->id,
                'amount'          => $amount,
                'type'            => $type === 'manual' ? Payment::TYPE_ORIGINAL : $type,
                'status'          => Payment::STATUS_COMPLETED,
                'comment'         => $comment,
            ]);

            DB::commit();

            return json_success(console_trans('common.updated_success'), ['refund_id' => $refund->id]);
        } catch (Exception $e) {
            DB::rollBack();

            return json_fail($e->getMessage());
        }
    }

    /**
     * Bulk change the status of multiple returns.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function bulkStatus(Request $request): mixed
    {
        $ids     = (array) $request->get('ids', []);
        $status  = (string) $request->get('status', '');
        $comment = (string) $request->get('comment', '');

        if (empty($ids)) {
            return json_fail(console_trans('common.select_items'));
        }
        if (! $status) {
            return json_fail(console_trans('common.invalid_parameters'));
        }

        $success = 0;
        $failed  = [];
        $returns = OrderReturn::query()->whereIn('id', $ids)->get();
        foreach ($returns as $orderReturn) {
            try {
                ReturnStateService::getInstance($orderReturn)->changeStatus($status, $comment, true);
                $success++;
            } catch (Exception $e) {
                $failed[] = $orderReturn->number;
            }
        }

        return json_success(console_trans('order_return.bulk_result', [
            'success' => $success,
            'failed'  => count($failed),
        ]), ['failed' => $failed]);
    }

    /**
     * Export order returns to an Excel file.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $filters = $request->all();
        $returns = OrderReturnRepo::getInstance()->builder($filters)
            ->with(['customer', 'reason'])
            ->orderByDesc('id')
            ->get();

        $rows = $returns->map(function (OrderReturn $item) {
            return [
                trans('common/rma.return_number')            => $item->number,
                console_trans('order_return.order_number')   => $item->order_number,
                console_trans('order_return.customer')       => $item->customer?->name ?? '',
                console_trans('order_return.email')          => $item->customer?->email ?? '',
                trans('front/return.product_name')           => $item->product_name,
                console_trans('order_return.product_sku')    => $item->product_sku,
                trans('front/return.quantity')               => $item->quantity,
                console_trans('return_reason.return_reason') => $item->reason_name,
                trans('front/return.opened')                 => $item->opened_format,
                trans('front/return.status')                 => $item->status_format,
                console_trans('order_return.comment')        => $item->comment,
                trans('front/return.created_at')             => (string) $item->created_at,
            ];
        });

        return (new FastExcel($rows))->download('order_returns_'.date('Ymd_His').'.xlsx');
    }
}
