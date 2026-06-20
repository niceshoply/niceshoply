<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BalancePay\Services;

use RuntimeException;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Services\StateMachineService;

class BalancePayService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 使用会员余额支付订单。
     *
     * @throws RuntimeException
     */
    public function pay(Order $order, int $customerId): void
    {
        if ($customerId <= 0 || (int) $order->customer_id !== $customerId) {
            throw new RuntimeException(__('BalancePay::common.not_owner'));
        }
        if ($order->status !== 'unpaid') {
            throw new RuntimeException(__('BalancePay::common.not_unpaid'));
        }

        $total = (float) $order->total;

        DB::transaction(function () use ($order, $customerId, $total) {
            /** @var Customer $customer */
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

            if ((float) $customer->balance < $total) {
                throw new RuntimeException(__('BalancePay::common.insufficient'));
            }

            $newBalance = round((float) $customer->balance - $total, 2);

            Transaction::query()->create([
                'customer_id' => $customerId,
                'amount'      => -$total,
                'type'        => Transaction::TYPE_CONSUMPTION,
                'comment'     => __('BalancePay::common.tx_comment', ['number' => $order->number]),
                'balance'     => $newBalance,
            ]);

            $customer->balance = $newBalance;
            $customer->save();

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => 'BAL'.$order->number,
                'amount'    => $total,
                'paid'      => true,
                'reference' => ['method' => 'balance_pay', 'customer_id' => $customerId],
            ]);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);
        });
    }
}
