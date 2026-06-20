<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Refund;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Models\Refund\Log;
use NiceShoply\Common\Repositories\Customer\TransactionRepo;
use NiceShoply\Common\Repositories\RefundRepo;
use NiceShoply\Common\Services\Promotion\CouponService;
use Throwable;

/**
 * 退款单服务（状态机闭环）。
 *
 * 状态流转：pending → processing → succeeded / failed；pending/processing → cancelled。
 * 退款方式：
 *  - balance：退回客户钱包余额（写 customer_transactions，复用 syncBalance）；
 *  - manual：人工线下退款，操作人确认后直接置成功；
 *  - original：原路退回，按订单支付网关解析 RefundableInterface 实现并调用。
 *
 * 退款成功联动：满额退款时回滚优惠券用量（CouponService::rollback），
 * 库存/积分回退通过 Hook 暴露给对应模块。
 */
final class RefundService
{
    /**
     * 可流转的状态机定义。
     */
    private const MACHINES = [
        'pending'    => ['processing', 'succeeded', 'failed', 'cancelled'],
        'processing' => ['succeeded', 'failed', 'cancelled'],
    ];

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 创建退款单（默认 pending）。
     *
     * @param  array  $data  order_id 必填；amount/method/reason/order_return_id/operator_id 可选
     * @return Refund
     *
     * @throws Exception
     */
    public function create(array $data): Refund
    {
        $orderId = (int) ($data['order_id'] ?? 0);
        $order   = Order::query()->findOrFail($orderId);

        $amount = round((float) ($data['amount'] ?? 0), 4);
        if ($amount <= 0) {
            throw new Exception(trans('common/refund.amount_invalid'));
        }

        // 防超额：已申请/已成功退款 + 本次 ≤ 订单总额
        $already = RefundRepo::getInstance()->succeededAmount($orderId);
        if ($already + $amount - (float) $order->total > 0.0001) {
            throw new Exception(trans('common/refund.amount_exceeds_order'));
        }

        $method = (string) ($data['method'] ?? Refund::METHOD_ORIGINAL);
        if (! in_array($method, Refund::METHODS, true)) {
            throw new Exception(trans('common/refund.method_invalid'));
        }

        if ($method === Refund::METHOD_BALANCE && empty($order->customer_id)) {
            throw new Exception(trans('common/refund.balance_need_customer'));
        }

        $refund = Refund::query()->create([
            'number'          => $this->generateNumber(),
            'order_id'        => $orderId,
            'order_return_id' => $data['order_return_id'] ?? null,
            'customer_id'     => (int) ($order->customer_id ?? 0),
            'amount'          => $amount,
            'currency_code'   => (string) ($order->currency_code ?? ''),
            'currency_value'  => (float) ($order->currency_value ?? 1),
            'method'          => $method,
            'status'          => 'pending',
            'gateway'         => $data['gateway'] ?? ($order->billing_method_code ?? null),
            'reason'          => $data['reason'] ?? null,
            'operator_id'     => (int) ($data['operator_id'] ?? 0),
        ]);

        $this->writeLog($refund, null, 'pending', (string) ($data['reason'] ?? ''), [], (int) $refund->operator_id);

        return $refund;
    }

    /**
     * 处理退款单：推进至 processing 并按方式执行，最终落成功或失败。
     *
     * @param  Refund  $refund
     * @param  int  $operatorId
     * @return Refund
     *
     * @throws Throwable
     */
    public function process(Refund $refund, int $operatorId = 0): Refund
    {
        $this->assertCanTransition($refund, 'processing');

        DB::beginTransaction();
        try {
            $this->transition($refund, 'processing', '', [], $operatorId);

            $result = match ($refund->method) {
                Refund::METHOD_BALANCE  => $this->refundToBalance($refund),
                Refund::METHOD_MANUAL   => RefundResult::success('manual'),
                Refund::METHOD_ORIGINAL => $this->refundToGateway($refund),
                default                 => RefundResult::failure(trans('common/refund.method_invalid')),
            };

            if ($result->success) {
                $this->markSucceeded($refund, $result, $operatorId);
            } else {
                $this->markFailed($refund, $result->message, $result->context, $operatorId);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $refund->refresh();
    }

    /**
     * 取消退款单。
     *
     * @param  Refund  $refund
     * @param  string  $comment
     * @param  int  $operatorId
     * @return Refund
     *
     * @throws Throwable
     */
    public function cancel(Refund $refund, string $comment = '', int $operatorId = 0): Refund
    {
        $this->assertCanTransition($refund, 'cancelled');

        DB::beginTransaction();
        try {
            $this->transition($refund, 'cancelled', $comment, [], $operatorId);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $refund->refresh();
    }

    /**
     * 退回钱包余额。
     *
     * @param  Refund  $refund
     * @return RefundResult
     */
    private function refundToBalance(Refund $refund): RefundResult
    {
        TransactionRepo::getInstance()->create([
            'customer_id' => $refund->customer_id,
            'amount'      => (float) $refund->amount,
            'type'        => Transaction::TYPE_REFUND,
            'comment'     => trans('common/refund.balance_comment', ['number' => $refund->number]),
        ]);

        return RefundResult::success('balance');
    }

    /**
     * 通过原支付网关退款。
     *
     * 支付插件实现 RefundableInterface 并经 Hook `service.refund.gateways` 注册；
     * 未匹配到实现时返回失败，提示改用人工/余额退款。
     *
     * @param  Refund  $refund
     * @return RefundResult
     */
    private function refundToGateway(Refund $refund): RefundResult
    {
        // 收集插件注册的退款实现：['gateway_code' => RefundableInterface, ...]
        $gateways = fire_hook_filter('service.refund.gateways', []);

        $handler = $gateways[$refund->gateway] ?? null;
        if (! $handler instanceof RefundableInterface) {
            return RefundResult::failure(trans('common/refund.gateway_unsupported', ['gateway' => (string) $refund->gateway]));
        }

        try {
            return $handler->refund($refund);
        } catch (Throwable $e) {
            return RefundResult::failure($e->getMessage());
        }
    }

    /**
     * 标记成功并执行联动副作用。
     *
     * @param  Refund  $refund
     * @param  RefundResult  $result
     * @param  int  $operatorId
     * @return void
     *
     * @throws Throwable
     */
    private function markSucceeded(Refund $refund, RefundResult $result, int $operatorId): void
    {
        $refund->gateway_ref  = $result->reference ?: $refund->gateway_ref;
        $refund->processed_at = Carbon::now();
        $this->transition($refund, 'succeeded', $result->message, $result->context, $operatorId);

        $refund->loadMissing('order');

        // 满额退款时回滚优惠券用量（部分退款不回滚，避免误放）
        if ($this->isFullRefund($refund)) {
            CouponService::getInstance()->rollback($refund->order_id);
        }

        // 暴露 Hook：库存回退（按策略）、积分回退（S3）等由对应模块订阅
        fire_hook_action('service.refund.succeeded', [
            'refund'      => $refund,
            'full_refund' => $this->isFullRefund($refund),
        ]);
    }

    /**
     * 标记失败。
     *
     * @param  Refund  $refund
     * @param  string  $reason
     * @param  array  $context
     * @param  int  $operatorId
     * @return void
     *
     * @throws Throwable
     */
    private function markFailed(Refund $refund, string $reason, array $context, int $operatorId): void
    {
        $this->transition($refund, 'failed', $reason, $context, $operatorId);
        fire_hook_action('service.refund.failed', ['refund' => $refund, 'reason' => $reason]);
    }

    /**
     * 判断是否满额退款（含本单的成功退款总额是否覆盖订单总额）。
     *
     * @param  Refund  $refund
     * @return bool
     */
    private function isFullRefund(Refund $refund): bool
    {
        $orderTotal = (float) ($refund->order->total ?? 0);
        if ($orderTotal <= 0) {
            return false;
        }

        $succeeded = (float) Refund::query()
            ->where('order_id', $refund->order_id)
            ->where('status', 'succeeded')
            ->sum('amount');

        return $succeeded + 0.0001 >= $orderTotal;
    }

    /**
     * 执行状态流转：更新状态字段 + 写流水。
     *
     * @param  Refund  $refund
     * @param  string  $to
     * @param  string  $comment
     * @param  array  $context
     * @param  int  $operatorId
     * @return void
     *
     * @throws Throwable
     */
    private function transition(Refund $refund, string $to, string $comment, array $context, int $operatorId): void
    {
        $from           = $refund->status;
        $refund->status = $to;
        if ($operatorId) {
            $refund->operator_id = $operatorId;
        }
        $refund->saveOrFail();

        $this->writeLog($refund, $from, $to, $comment, $context, $operatorId);
    }

    /**
     * 写退款流水。
     *
     * @param  Refund  $refund
     * @param  string|null  $from
     * @param  string  $to
     * @param  string  $comment
     * @param  array  $context
     * @param  int  $operatorId
     * @return void
     */
    private function writeLog(Refund $refund, ?string $from, string $to, string $comment, array $context, int $operatorId): void
    {
        Log::query()->create([
            'refund_id'   => $refund->id,
            'from_status' => $from,
            'to_status'   => $to,
            'comment'     => $comment,
            'context'     => $context ?: null,
            'operator_id' => $operatorId,
        ]);
    }

    /**
     * 校验状态机是否允许从当前状态流转到目标状态。
     *
     * @param  Refund  $refund
     * @param  string  $to
     * @return void
     *
     * @throws Exception
     */
    private function assertCanTransition(Refund $refund, string $to): void
    {
        $allowed = self::MACHINES[$refund->status] ?? [];
        if (! in_array($to, $allowed, true)) {
            throw new Exception(trans('common/refund.invalid_transition', [
                'from' => $refund->status,
                'to'   => $to,
            ]));
        }
    }

    /**
     * 生成唯一退款单号。
     *
     * @return string
     */
    private function generateNumber(): string
    {
        do {
            $number = 'RF'.date('YmdHis').str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        } while (Refund::query()->where('number', $number)->exists());

        return $number;
    }
}
