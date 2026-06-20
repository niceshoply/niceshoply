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
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Repositories\RefundRepo;
use NiceShoply\Common\Services\Refund\RefundService;

/**
 * 退款单后台控制器。
 */
class RefundController extends BaseController
{
    public function index(Request $request): mixed
    {
        $refunds = RefundRepo::getInstance()->list($request->all());
        $refunds->load(['order', 'customer']);

        $data = [
            'criteria' => RefundRepo::getCriteria(),
            'refunds'  => $refunds,
        ];

        return nice_view('console::refunds.index', $data);
    }

    /**
     * 退款单详情（含流水）。
     *
     * @param  Refund  $refund
     * @return mixed
     */
    public function show(Refund $refund): mixed
    {
        $refund->load(['order', 'customer', 'orderReturn', 'logs']);

        return nice_view('console::refunds.show', ['refund' => $refund]);
    }

    /**
     * 创建退款单（可来自订单或退货单）。
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $refund = RefundService::getInstance()->create([
                'order_id'        => (int) $request->input('order_id'),
                'order_return_id' => $request->input('order_return_id') ?: null,
                'amount'          => (float) $request->input('amount', 0),
                'method'          => (string) $request->input('method', Refund::METHOD_ORIGINAL),
                'reason'          => (string) $request->input('reason', ''),
                'operator_id'     => (int) (current_admin()->id ?? 0),
            ]);

            return redirect(console_route('refunds.show', [$refund->id]))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 处理退款单（执行退款并落成功/失败）。
     *
     * @param  Refund  $refund
     * @return mixed
     */
    public function process(Refund $refund): mixed
    {
        try {
            $refund = RefundService::getInstance()->process($refund, (int) (current_admin()->id ?? 0));

            if ($refund->status === 'failed') {
                return json_fail($refund->logs()->first()->comment ?? console_trans('common.error'));
            }

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 取消退款单。
     *
     * @param  Request  $request
     * @param  Refund  $refund
     * @return mixed
     */
    public function cancel(Request $request, Refund $refund): mixed
    {
        try {
            RefundService::getInstance()->cancel($refund, (string) $request->input('comment', ''), (int) (current_admin()->id ?? 0));

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
