<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Services;

use Exception;
use Illuminate\Support\Carbon;
use Plugin\Coupon\Models\Coupon;
use Plugin\Coupon\Models\CouponUsage;

class CouponService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 校验券是否可用，可用则返回 Coupon，否则抛出异常。
     *
     * @throws Exception
     */
    public function validate(string $code, float $subtotal, int $customerId = 0): Coupon
    {
        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon || ! $coupon->active) {
            throw new Exception(__('Coupon::common.invalid'));
        }

        $now = Carbon::now();
        if ($coupon->start_at && $now->lt($coupon->start_at)) {
            throw new Exception(__('Coupon::common.not_started'));
        }
        if ($coupon->end_at && $now->gt($coupon->end_at)) {
            throw new Exception(__('Coupon::common.expired'));
        }

        if ($coupon->min_amount > 0 && $subtotal < $coupon->min_amount) {
            throw new Exception(__('Coupon::common.err_min_amount', ['amount' => currency_format($coupon->min_amount)]));
        }

        if ($coupon->usage_limit > 0 && $coupon->used_count >= $coupon->usage_limit) {
            throw new Exception(__('Coupon::common.used_up'));
        }

        if ($customerId > 0 && $coupon->per_customer_limit > 0) {
            $usedByCustomer = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->where('customer_id', $customerId)
                ->count();
            if ($usedByCustomer >= $coupon->per_customer_limit) {
                throw new Exception(__('Coupon::common.err_per_customer'));
            }
        }

        return $coupon;
    }

    /**
     * 计算折扣金额（正数）。free_shipping 由调用方传入运费值。
     */
    public function computeDiscount(Coupon $coupon, float $subtotal, float $shippingFee = 0): float
    {
        $discount = match ($coupon->type) {
            'fixed'         => min($coupon->value, $subtotal),
            'percent'       => $subtotal * $coupon->value / 100,
            'free_shipping' => $shippingFee,
            default         => 0,
        };

        if ($coupon->type === 'percent' && $coupon->max_discount > 0) {
            $discount = min($discount, $coupon->max_discount);
        }

        return round(max($discount, 0), 2);
    }

    /**
     * 不抛异常版本：用于结算费用计算，校验失败返回 null。
     */
    public function tryValidate(string $code, float $subtotal, int $customerId = 0): ?Coupon
    {
        try {
            return $this->validate($code, $subtotal, $customerId);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * 下单成功后核销：记录使用并累加次数。
     */
    public function redeemForOrder($order, array $checkout): void
    {
        if (! $order) {
            return;
        }

        $reference = $checkout['reference'] ?? [];
        $code      = $reference['coupon_code'] ?? null;
        if (! $code) {
            return;
        }

        $coupon = Coupon::query()->where('code', $code)->first();
        if (! $coupon) {
            return;
        }

        // 计算本单优惠（从订单费用项里取 coupon 费用，若无则按当前规则估算）
        $discount = 0;
        if (method_exists($order, 'fees')) {
            $fee      = $order->fees()->where('code', 'coupon')->first();
            $discount = $fee ? abs((float) $fee->total) : 0;
        }

        CouponUsage::query()->create([
            'coupon_id'   => $coupon->id,
            'code'        => $coupon->code,
            'customer_id' => $order->customer_id ?? 0,
            'order_id'    => $order->id ?? 0,
            'discount'    => $discount,
        ]);

        $coupon->increment('used_count');
    }
}
