<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Recharge\Services;

use RuntimeException;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\OrderRepo;
use Plugin\Recharge\Models\RechargeOrder;
use Plugin\Recharge\Models\RechargePlan;

class RechargeService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function activePlans()
    {
        return RechargePlan::query()->where('is_active', true)->orderBy('sort')->orderByDesc('id')->get();
    }

    /**
     * 创建充值订单（复用订单系统，状态 unpaid，走现有支付网关）。
     *
     * @return Order
     * @throws RuntimeException
     */
    public function createRechargeOrder(int $customerId, ?int $planId, float $customAmount, string $billingMethodCode, string $billingMethodName = ''): Order
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('Recharge::common.need_login'));
        }
        if ($billingMethodCode === '') {
            throw new RuntimeException(__('Recharge::common.need_billing'));
        }

        $amount = 0.0;
        $bonus  = 0.0;

        if ($planId) {
            $plan = RechargePlan::query()->where('is_active', true)->findOrFail($planId);
            $amount = (float) $plan->amount;
            $bonus  = (float) $plan->bonus;
        } else {
            if (! (bool) plugin_setting('recharge', 'allow_custom', false)) {
                throw new RuntimeException(__('Recharge::common.custom_disabled'));
            }
            $amount = round($customAmount, 2);
        }

        if ($amount <= 0) {
            throw new RuntimeException(__('Recharge::common.invalid_amount'));
        }

        return DB::transaction(function () use ($customerId, $amount, $bonus, $billingMethodCode, $billingMethodName) {
            $order = OrderRepo::getInstance()->create([
                'customer_id'          => $customerId,
                'shipping_address_id'  => 0,
                'billing_address_id'   => 0,
                'total'                => $amount,
                'status'               => 'unpaid',
                'billing_method_code'  => $billingMethodCode,
                'billing_method_name'  => $billingMethodName ?: $billingMethodCode,
                'comment'              => __('Recharge::common.order_comment'),
            ]);

            RechargeOrder::query()->create([
                'order_id'    => $order->id,
                'customer_id' => $customerId,
                'amount'      => $amount,
                'bonus'       => $bonus,
                'status'      => 'pending',
            ]);

            return $order;
        });
    }

    /**
     * 订单支付成功后入账（由 PAID 状态钩子触发，幂等）。
     */
    public function handleOrderPaid(?Order $order): void
    {
        if (! $order) {
            return;
        }

        DB::transaction(function () use ($order) {
            /** @var RechargeOrder|null $recharge */
            $recharge = RechargeOrder::query()
                ->where('order_id', $order->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $recharge) {
                return;
            }

            $credit   = round((float) $recharge->amount + (float) $recharge->bonus, 2);
            $customer = Customer::query()->lockForUpdate()->find($recharge->customer_id);
            if (! $customer) {
                return;
            }

            $newBalance = round((float) $customer->balance + $credit, 2);

            Transaction::query()->create([
                'customer_id' => $customer->id,
                'amount'      => $credit,
                'type'        => Transaction::TYPE_RECHARGE,
                'comment'     => __('Recharge::common.tx_comment', ['number' => $order->number]),
                'balance'     => $newBalance,
            ]);

            $customer->balance = $newBalance;
            $customer->save();

            $recharge->status      = 'credited';
            $recharge->credited_at = now();
            $recharge->save();
        });
    }
}
