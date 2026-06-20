<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Subscription\Services;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\Subscription\Models\Subscription;

class SubscriptionService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function listForCustomer(int $customerId)
    {
        return Subscription::query()
            ->where('customer_id', $customerId)
            ->where('status', '!=', Subscription::STATUS_CANCELLED)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 创建订阅。
     *
     * @throws RuntimeException
     */
    public function subscribe(int $customerId, array $data): Subscription
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('Subscription::common.need_login'));
        }

        $skuCode = (string) ($data['product_sku'] ?? '');
        /** @var Sku|null $sku */
        $sku = Sku::query()->where('code', $skuCode)->first();
        if (! $sku) {
            throw new RuntimeException(__('Subscription::common.sku_not_found'));
        }

        $unit = in_array(($data['interval_unit'] ?? ''), ['day', 'week', 'month'], true) ? $data['interval_unit'] : 'month';
        $count = max(1, (int) ($data['interval_count'] ?? 1));
        $qty   = max(1, (int) ($data['quantity'] ?? 1));

        $paymentMode = in_array(($data['payment_mode'] ?? ''), ['reminder', 'auto_balance'], true)
            ? $data['payment_mode']
            : (string) plugin_setting('subscription', 'default_payment_mode', 'reminder');

        return Subscription::query()->create([
            'customer_id'         => $customerId,
            'product_id'          => (int) $sku->product_id,
            'product_sku'         => $sku->code,
            'name'                => optional($sku->product)->name ?? $sku->code,
            'image'               => method_exists($sku, 'getImagePath') ? $sku->getImagePath() : ($sku->image ?? ''),
            'price'               => (float) $sku->getFinalPrice(),
            'quantity'            => $qty,
            'interval_unit'       => $unit,
            'interval_count'      => $count,
            'next_run_at'         => $this->computeNext(now(), $unit, $count),
            'shipping_address_id' => (int) ($data['shipping_address_id'] ?? 0),
            'billing_address_id'  => (int) ($data['billing_address_id'] ?? ($data['shipping_address_id'] ?? 0)),
            'payment_mode'        => $paymentMode,
            'status'              => Subscription::STATUS_ACTIVE,
        ]);
    }

    public function setStatus(int $customerId, int $id, string $status): Subscription
    {
        if (! in_array($status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_PAUSED, Subscription::STATUS_CANCELLED], true)) {
            throw new RuntimeException(__('Subscription::common.invalid_status'));
        }

        /** @var Subscription $sub */
        $sub = Subscription::query()->where('customer_id', $customerId)->findOrFail($id);
        $sub->status = $status;

        // 从暂停恢复时，若已过期则顺延到下一个周期
        if ($status === Subscription::STATUS_ACTIVE && (! $sub->next_run_at || $sub->next_run_at->isPast())) {
            $sub->next_run_at = $this->computeNext(now(), $sub->interval_unit, $sub->interval_count);
        }
        $sub->save();

        return $sub;
    }

    /**
     * 扫描到期订阅并生成订单。
     *
     * @return array{processed:int, paid:int, failed:int}
     */
    public function runDue(): array
    {
        $stats = ['processed' => 0, 'paid' => 0, 'failed' => 0];

        Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($subs) use (&$stats) {
                foreach ($subs as $sub) {
                    $stats['processed']++;
                    try {
                        $paid = $this->generateOrder($sub);
                        if ($paid) {
                            $stats['paid']++;
                        }
                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        Log::error('subscription.run.failed', ['id' => $sub->id, 'error' => $e->getMessage()]);
                    }
                }
            });

        return $stats;
    }

    /**
     * 为单个订阅生成一期订单；返回是否已自动支付。
     */
    public function generateOrder(Subscription $sub): bool
    {
        /** @var Sku|null $sku */
        $sku = Sku::query()->where('code', $sub->product_sku)->first();
        if (! $sku) {
            // SKU 不存在则暂停该订阅，避免反复失败
            $sub->status = Subscription::STATUS_PAUSED;
            $sub->save();

            return false;
        }

        $price = (float) $sku->getFinalPrice();
        $total = round($price * $sub->quantity, 2);

        return DB::transaction(function () use ($sub, $sku, $price, $total) {
            $billingCode = $sub->payment_mode === 'auto_balance' ? 'balance_pay' : 'subscription';

            $order = OrderRepo::getInstance()->create([
                'customer_id'         => $sub->customer_id,
                'shipping_address_id' => (int) $sub->shipping_address_id,
                'billing_address_id'  => (int) $sub->billing_address_id,
                'total'               => $total,
                'status'              => 'unpaid',
                'billing_method_code' => $billingCode,
                'billing_method_name' => __('Subscription::common.billing_name'),
                'comment'             => __('Subscription::common.order_comment', ['name' => $sub->name]),
            ]);

            Item::query()->create([
                'order_id'      => $order->id,
                'product_id'    => (int) $sub->product_id,
                'order_number'  => $order->number,
                'product_sku'   => $sub->product_sku,
                'variant_label' => '',
                'name'          => $sub->name,
                'image'         => $sub->image,
                'quantity'      => $sub->quantity,
                'price'         => $price,
                'item_type'     => 'normal',
            ]);

            $paid = false;
            if ($sub->payment_mode === 'auto_balance') {
                $paid = $this->tryPayWithBalance($order, (int) $sub->customer_id, $total);
            }

            $this->advance($sub, $order->id);
            $this->notifyCustomer($sub, $order, $paid);

            return $paid;
        });
    }

    protected function tryPayWithBalance(Order $order, int $customerId, float $total): bool
    {
        /** @var Customer|null $customer */
        $customer = Customer::query()->lockForUpdate()->find($customerId);
        if (! $customer || (float) $customer->balance < $total) {
            return false;
        }

        $newBalance = round((float) $customer->balance - $total, 2);

        Transaction::query()->create([
            'customer_id' => $customerId,
            'amount'      => -$total,
            'type'        => Transaction::TYPE_CONSUMPTION,
            'comment'     => __('Subscription::common.tx_comment', ['number' => $order->number]),
            'balance'     => $newBalance,
        ]);

        $customer->balance = $newBalance;
        $customer->save();

        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
            'charge_id' => 'SUB'.$order->number,
            'amount'    => $total,
            'paid'      => true,
            'reference' => ['method' => 'subscription_balance', 'customer_id' => $customerId],
        ]);

        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

        return true;
    }

    protected function advance(Subscription $sub, int $orderId): void
    {
        $sub->cycles_done = (int) $sub->cycles_done + 1;
        $sub->last_order_id = $orderId;
        $sub->last_run_at = now();
        $sub->next_run_at = $this->computeNext(now(), $sub->interval_unit, $sub->interval_count);
        $sub->save();
    }

    protected function notifyCustomer(Subscription $sub, Order $order, bool $paid): void
    {
        if (! class_exists(\Plugin\NotifyCenter\Services\NotifyService::class)) {
            return;
        }

        $key = $paid ? 'Subscription::common.notify_paid' : 'Subscription::common.notify_unpaid';

        try {
            \Plugin\NotifyCenter\Services\NotifyService::getInstance()->notify(
                (int) $sub->customer_id,
                __('Subscription::common.notify_title'),
                __($key, ['name' => $sub->name, 'number' => $order->number])
            );
        } catch (\Throwable $e) {
            Log::warning('subscription.notify.failed', ['error' => $e->getMessage()]);
        }
    }

    protected function computeNext(Carbon $from, string $unit, int $count): Carbon
    {
        $from = $from->copy();

        return match ($unit) {
            'day'   => $from->addDays($count),
            'week'  => $from->addWeeks($count),
            default => $from->addMonths($count),
        };
    }
}
