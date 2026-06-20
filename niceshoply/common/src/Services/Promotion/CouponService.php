<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Promotion;

use Exception;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\CouponUsage;
use NiceShoply\Common\Models\PromotionOrderLog;
use NiceShoply\Common\Repositories\CouponRepo;
use NiceShoply\Common\Services\CheckoutService;

/**
 * 优惠券服务。
 *
 * 职责：
 *  1. validate()：校验券码有效性（启用/时间/门槛/限领/限用）并计算折扣；
 *  2. redeem()：在订单事务内写核销记录（唯一约束防重）并原子累加用量，
 *     累加后复核上限——这是「并发不超发」的关键保证；
 *  3. rollback()：订单取消/退款时回退用量并清理记录。
 */
final class CouponService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 校验券码并计算折扣。
     *
     * @param  string  $code
     * @param  CheckoutService  $checkout
     * @return array{valid: bool, message: string, coupon: Coupon|null, discount: float, free_shipping: bool}
     */
    public function validate(string $code, CheckoutService $checkout): array
    {
        $coupon = CouponRepo::getInstance()->findByCode($code);

        if (! $coupon || ! $coupon->active) {
            return $this->fail(trans('front/coupon.invalid'));
        }

        $now = Carbon::now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return $this->fail(trans('front/coupon.not_started'));
        }
        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            return $this->fail(trans('front/coupon.expired'));
        }

        // 总量上限
        if ($coupon->total_limit > 0 && $coupon->used_count >= $coupon->total_limit) {
            return $this->fail(trans('front/coupon.used_up'));
        }

        // 单客户限用次数
        $customerId = $checkout->getCustomerId();
        if ($coupon->per_customer_limit > 0 && $customerId > 0) {
            $usedByCustomer = CouponRepo::getInstance()->customerUsageCount($coupon->id, $customerId);
            if ($usedByCustomer >= $coupon->per_customer_limit) {
                return $this->fail(trans('front/coupon.customer_limit'));
            }
        }

        // 门槛金额
        $subtotal = $checkout->getSubTotal();
        if ($coupon->min_amount > 0 && $subtotal < (float) $coupon->min_amount) {
            return $this->fail(trans('front/coupon.min_amount', ['amount' => currency_format($coupon->min_amount)]));
        }

        $discount     = $this->calculateDiscount($coupon, $subtotal);
        $freeShipping = $coupon->type === 'free_shipping';

        return [
            'valid'         => true,
            'message'       => trans('front/coupon.applied'),
            'coupon'        => $coupon,
            'discount'      => $discount,
            'free_shipping' => $freeShipping,
        ];
    }

    /**
     * 计算券折扣金额（正数）。
     *
     * @param  Coupon  $coupon
     * @param  float  $subtotal
     * @return float
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $value   = (float) $coupon->value;
        $decimal = currency_decimal_place();

        $discount = match ($coupon->type) {
            'percent' => $subtotal * ($value / 100),
            'fixed'   => $value,
            default   => 0.0, // free_shipping 不抵扣小计
        };

        $discount = min($discount, $subtotal);

        return round(max(0.0, $discount), $decimal);
    }

    /**
     * 核销优惠券（必须在订单事务内调用）。
     *
     * 写入唯一核销记录 + 原子累加用量 + 复核上限，超限则抛异常触发整单回滚。
     *
     * @param  Coupon  $coupon
     * @param  mixed  $order
     * @param  int  $customerId
     * @param  float  $discountAmount
     * @return void
     * @throws Exception
     */
    public function redeem(Coupon $coupon, mixed $order, int $customerId, float $discountAmount): void
    {
        // 唯一约束 (coupon_id, order_id) 防止同单重复核销
        CouponUsage::query()->create([
            'coupon_id'       => $coupon->id,
            'customer_id'     => $customerId,
            'order_id'        => $order->id,
            'discount_amount' => $discountAmount,
            'used_at'         => Carbon::now(),
        ]);

        // 原子累加，避免并发竞态
        CouponRepo::getInstance()->incrementUsed($coupon->id);

        // 落库订单优惠券流水
        PromotionOrderLog::query()->create([
            'order_id'        => $order->id,
            'promotion_id'    => $coupon->promotion_id,
            'coupon_id'       => $coupon->id,
            'code'            => $coupon->code,
            'discount_amount' => $discountAmount,
            'snapshot'        => [
                'type'  => $coupon->type,
                'value' => (float) $coupon->value,
            ],
        ]);

        // 累加后复核总量上限：若超发则抛异常回滚整单（并发不超发的最终保证）
        $fresh = Coupon::query()->find($coupon->id);
        if ($fresh && $fresh->total_limit > 0 && $fresh->used_count > $fresh->total_limit) {
            throw new Exception(trans('front/coupon.used_up'));
        }
    }

    /**
     * 回滚某订单的优惠券核销（订单取消/退款时调用）。
     *
     * @param  mixed  $order
     * @return void
     */
    public function rollback(mixed $order): void
    {
        $orderId = is_object($order) ? $order->id : (int) $order;
        $repo    = CouponRepo::getInstance();

        $usages = CouponUsage::query()->where('order_id', $orderId)->get();
        foreach ($usages as $usage) {
            $repo->decrementUsed((int) $usage->coupon_id);
        }

        CouponUsage::query()->where('order_id', $orderId)->delete();

        PromotionOrderLog::query()
            ->where('order_id', $orderId)
            ->whereNotNull('coupon_id')
            ->delete();
    }

    /**
     * 构造校验失败返回结构。
     *
     * @param  string  $message
     * @return array{valid: bool, message: string, coupon: null, discount: float, free_shipping: bool}
     */
    private function fail(string $message): array
    {
        return [
            'valid'         => false,
            'message'       => $message,
            'coupon'        => null,
            'discount'      => 0.0,
            'free_shipping' => false,
        ];
    }
}
