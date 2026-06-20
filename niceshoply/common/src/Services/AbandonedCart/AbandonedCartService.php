<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\AbandonedCart;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\AbandonedCart;
use NiceShoply\Common\Models\CartItem;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Notifications\AbandonedCartNotification;
use NiceShoply\Common\Repositories\AbandonedCartRepo;
use NiceShoply\Common\Repositories\CouponRepo;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\CartService;
use NiceShoply\Common\Services\Notification\NotificationManager;
use Throwable;

/**
 * 弃购扫描、召回通知与转化统计。
 */
class AbandonedCartService extends BaseService
{
    /**
     * 扫描闲置购物车并发送召回。
     *
     * @return array{scanned: int, reminded: int, skipped: int}
     */
    public function scanAndRemind(): array
    {
        if (! $this->isEnabled()) {
            return ['scanned' => 0, 'reminded' => 0, 'skipped' => 0];
        }

        $groups   = $this->findStaleCartGroups();
        $reminded = 0;
        $skipped  = 0;

        foreach ($groups as $cartKey => $items) {
            try {
                if ($this->processCartGroup($cartKey, $items)) {
                    $reminded++;
                } else {
                    $skipped++;
                }
            } catch (Throwable $e) {
                Log::warning('弃购召回处理失败：'.$e->getMessage(), ['cart_key' => $cartKey]);
                $skipped++;
            }
        }

        return [
            'scanned'  => $groups->count(),
            'reminded' => $reminded,
            'skipped'  => $skipped,
        ];
    }

    /**
     * 订单下单后标记转化为已召回成功。
     */
    public function markConverted(Order $order): void
    {
        if ($order->customer_id <= 0) {
            return;
        }

        $cartKey = $this->buildCartKey((int) $order->customer_id, '');
        $record  = AbandonedCartRepo::getInstance()->findByCartKey($cartKey);

        if (! $record || $record->converted) {
            return;
        }

        $record->converted          = true;
        $record->converted_order_id = $order->id;
        $record->converted_at       = Carbon::now();
        $record->save();
    }

    /**
     * 召回转化统计。
     *
     * @return array{total: int, converted: int, rate: float, reminded: int}
     */
    public function getStats(?string $start = null, ?string $end = null): array
    {
        $query = AbandonedCart::query();

        if ($start) {
            $query->where('created_at', '>=', $start);
        }
        if ($end) {
            $query->where('created_at', '<=', $end.' 23:59:59');
        }

        $total     = (clone $query)->count();
        $converted = (clone $query)->where('converted', true)->count();
        $reminded  = (clone $query)->where('reminder_count', '>', 0)->count();

        return [
            'total'     => $total,
            'converted' => $converted,
            'reminded'  => $reminded,
            'rate'      => $total > 0 ? round($converted / $total * 100, 1) : 0.0,
        ];
    }

    public function isEnabled(): bool
    {
        return (bool) system_setting('abandoned_cart_enabled', false);
    }

    public function getIdleHours(): int
    {
        return max(1, (int) system_setting('abandoned_cart_idle_hours', 24));
    }

    public function getMaxReminders(): int
    {
        return max(1, (int) system_setting('abandoned_cart_max_reminders', 3));
    }

    public function getReminderIntervalHours(): int
    {
        return max(1, (int) system_setting('abandoned_cart_reminder_interval_hours', 24));
    }

    /**
     * @return Collection<string, Collection<int, CartItem>>
     */
    private function findStaleCartGroups(): Collection
    {
        $threshold = Carbon::now()->subHours($this->getIdleHours());

        $items = CartItem::query()
            ->with(['product', 'productSku', 'customer'])
            ->where('updated_at', '<=', $threshold)
            ->where(function ($query) {
                $query->where('customer_id', '>', 0)
                    ->orWhere('guest_id', '!=', '');
            })
            ->get();

        return $items->groupBy(function (CartItem $item) {
            return $this->buildCartKey((int) $item->customer_id, (string) $item->guest_id);
        });
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    private function processCartGroup(string $cartKey, Collection $items): bool
    {
        if ($items->isEmpty()) {
            return false;
        }

        $first      = $items->first();
        $customerId = (int) ($first->customer_id ?? 0);
        $guestId    = (string) ($first->guest_id ?? '');

        $cartService = CartService::getInstance($customerId, $guestId);
        $cartList    = $cartService->getCartList();
        if (empty($cartList)) {
            return false;
        }

        $email = $this->resolveEmail($customerId, $first);
        if ($email === '') {
            return false;
        }

        $amount = round(collect($cartList)->sum('subtotal'), currency_decimal_place());

        $record = AbandonedCartRepo::getInstance()->findByCartKey($cartKey);
        if (! $record) {
            $record = new AbandonedCart([
                'cart_key'    => $cartKey,
                'customer_id' => $customerId,
                'guest_id'    => $guestId,
            ]);
        }

        if ($record->converted) {
            return false;
        }

        $record->email         = $email;
        $record->cart_snapshot = $cartList;
        $record->cart_total    = $amount;
        $record->currency_code = setting_currency_code();
        $record->save();

        if (! $this->shouldSendReminder($record)) {
            return false;
        }

        $this->attachCouponIfNeeded($record);
        $this->sendReminder($record);

        $record->reminder_count++;
        $record->last_reminded_at = Carbon::now();
        $record->save();

        return true;
    }

    private function shouldSendReminder(AbandonedCart $record): bool
    {
        if ($record->reminder_count >= $this->getMaxReminders()) {
            return false;
        }

        if (! $record->last_reminded_at) {
            return true;
        }

        return $record->last_reminded_at->lte(Carbon::now()->subHours($this->getReminderIntervalHours()));
    }

    private function attachCouponIfNeeded(AbandonedCart $record): void
    {
        if ($record->coupon_id > 0) {
            return;
        }

        if (! (bool) system_setting('abandoned_cart_coupon_enabled', false)) {
            return;
        }

        if ($record->customer_id <= 0) {
            return;
        }

        $codes = CouponRepo::getInstance()->batchGenerate([
            'type'               => system_setting('abandoned_cart_coupon_type', 'percent'),
            'value'              => (float) system_setting('abandoned_cart_coupon_value', 10),
            'min_amount'         => (float) system_setting('abandoned_cart_coupon_min_amount', 0),
            'total_limit'        => 1,
            'per_customer_limit' => 1,
            'active'             => true,
        ], 1, 'AC');

        if (empty($codes)) {
            return;
        }

        $coupon = Coupon::query()->where('code', $codes[0])->first();
        if ($coupon) {
            $record->coupon_id   = $coupon->id;
            $record->coupon_code = $coupon->code;
        }
    }

    private function sendReminder(AbandonedCart $record): void
    {
        $customer = $record->customer_id > 0
            ? Customer::query()->find($record->customer_id)
            : null;

        if ($customer && $customer->email) {
            $customer->notify(new AbandonedCartNotification($record));
        }

        // 运营通知渠道（Webhook/Slack 等）
        if (NotificationManager::isEventEnabled('abandoned_cart')) {
            $content = "> **客户:** {$record->email}\n"
                .'> **购物车金额:** '.currency_format((float) $record->cart_total, $record->currency_code)."\n"
                .'> **提醒次数:** '.($record->reminder_count + 1);
            if ($record->coupon_code) {
                $content .= "\n> **召回券:** {$record->coupon_code}";
            }
            NotificationManager::getInstance()->notify('弃购召回', $content, 'info');
        }

        fire_hook_action('service.abandoned_cart.remind', [
            'abandoned_cart' => $record,
            'customer'       => $customer,
        ]);
    }

    private function resolveEmail(int $customerId, CartItem $item): string
    {
        if ($customerId > 0) {
            $customer = Customer::query()->find($customerId);

            return (string) ($customer->email ?? '');
        }

        return '';
    }

    private function buildCartKey(int $customerId, string $guestId): string
    {
        if ($customerId > 0) {
            return 'c:'.$customerId;
        }

        return 'g:'.($guestId ?: '0');
    }
}
