<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Promotion;

use Illuminate\Support\Collection;
use NiceShoply\Common\Models\Promotion;
use NiceShoply\Common\Models\PromotionOrderLog;
use NiceShoply\Common\Repositories\PromotionRepo;
use NiceShoply\Common\Services\CheckoutService;

/**
 * 促销引擎服务。
 *
 * 职责：
 *  1. 依据购物车上下文（金额/件数/客户分组）筛选可用促销活动；
 *  2. 解析活动规则计算折扣项，处理 priority（优先级）与 exclusive（互斥）；
 *  3. 折扣以正数金额输出，由 Fee\Discount 转为负费用项注入结账金额闭环；
 *  4. 订单确认后落库促销应用流水，并提供并发安全的次数累加。
 *
 * 扩展点（供 marketing 插件注入拼团/秒杀等玩法）：
 *  - Hook filter `service.promotion.applicable`：调整可用活动集合；
 *  - Hook filter `service.promotion.discount`：调整最终折扣项列表。
 */
final class PromotionService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 获取适用于当前结账上下文的促销活动（已通过条件判定）。
     *
     * @param  CheckoutService  $checkout
     * @return Collection<int, Promotion>
     */
    public function getApplicable(CheckoutService $checkout): Collection
    {
        $customer = $checkout->getCustomer();
        $groupId  = (int) ($customer->customer_group_id ?? 0);

        $promotions = PromotionRepo::getInstance()->getActiveForCheckout($groupId);

        $subtotal = $checkout->getSubTotal();
        $quantity = $checkout->getTotalNumber();

        $applicable = $promotions->filter(function (Promotion $promotion) use ($subtotal, $quantity) {
            return $this->matchConditions($promotion, $subtotal, $quantity);
        })->values();

        return fire_hook_filter('service.promotion.applicable', $applicable);
    }

    /**
     * 计算最终折扣项列表。
     *
     * 按优先级降序依次应用；命中 exclusive 活动后停止叠加；
     * 累计折扣不超过当前小计（剩余可抵额逐项扣减）。
     *
     * @param  CheckoutService  $checkout
     * @return array<int, array<string, mixed>> 折扣项（amount 为正数）
     */
    public function calculate(CheckoutService $checkout): array
    {
        $applicable = $this->getApplicable($checkout);
        $subtotal   = $checkout->getSubTotal();
        $remaining  = $subtotal;
        $decimal    = currency_decimal_place();

        $result = [];

        foreach ($applicable as $promotion) {
            $freeShipping = $promotion->action_type === 'free_shipping';

            if ($remaining <= 0 && ! $freeShipping) {
                break;
            }

            $discount = $freeShipping ? 0.0 : $this->computeDiscount($promotion, $subtotal);

            // 既无金额折扣也非免运费，跳过
            if ($discount <= 0 && ! $freeShipping) {
                continue;
            }

            // 封顶：不超过剩余可抵额
            if ($discount > 0) {
                $discount = round(min($discount, $remaining), $decimal);
                $remaining -= $discount;
            }

            $result[] = [
                'type'          => 'promotion',
                'promotion_id'  => $promotion->id,
                'coupon_id'     => null,
                'code'          => $promotion->name,
                'label'         => $promotion->label,
                'amount'        => $discount,
                'free_shipping' => $freeShipping,
                'snapshot'      => [
                    'action_type'    => $promotion->action_type,
                    'actions'        => $promotion->actions,
                    'condition_type' => $promotion->condition_type,
                    'conditions'     => $promotion->conditions,
                ],
            ];

            if ($freeShipping) {
                $checkout->markFreeShipping();
            }

            // 互斥：命中后不再叠加后续活动
            if ($promotion->exclusive) {
                break;
            }
        }

        return fire_hook_filter('service.promotion.discount', $result);
    }

    /**
     * 判定活动条件是否满足。
     *
     * @param  Promotion  $promotion
     * @param  float  $subtotal
     * @param  int  $quantity
     * @return bool
     */
    public function matchConditions(Promotion $promotion, float $subtotal, int $quantity): bool
    {
        $conditions = $promotion->conditions ?? [];

        return match ($promotion->condition_type) {
            'min_amount' => $subtotal >= (float) ($conditions['min_amount'] ?? 0),
            'min_qty'    => $quantity >= (int) ($conditions['min_qty'] ?? 0),
            'tiered'     => $this->resolveTier($conditions['tiers'] ?? [], $subtotal) !== null,
            default      => true, // none
        };
    }

    /**
     * 计算单个活动的折扣金额（正数）。
     *
     * @param  Promotion  $promotion
     * @param  float  $subtotal
     * @return float
     */
    public function computeDiscount(Promotion $promotion, float $subtotal): float
    {
        $actions = $promotion->actions ?? [];

        // 阶梯促销：由命中的阶梯提供优惠值
        if ($promotion->condition_type === 'tiered') {
            $tier = $this->resolveTier($actions['tiers'] ?? ($promotion->conditions['tiers'] ?? []), $subtotal);
            if ($tier === null) {
                return 0.0;
            }
            $value = (float) ($tier['value'] ?? 0);

            return $this->applyValue($promotion->action_type, $value, $subtotal, $actions);
        }

        $value = (float) ($actions['value'] ?? 0);

        return $this->applyValue($promotion->action_type, $value, $subtotal, $actions);
    }

    /**
     * 按优惠类型把数值转为实际折扣金额。
     *
     * @param  string  $actionType
     * @param  float  $value
     * @param  float  $subtotal
     * @param  array  $actions
     * @return float
     */
    private function applyValue(string $actionType, float $value, float $subtotal, array $actions): float
    {
        if ($value <= 0) {
            return 0.0;
        }

        $discount = match ($actionType) {
            'percent' => $subtotal * ($value / 100),
            'fixed'   => $value,
            default   => 0.0,
        };

        // 百分比折扣可设置封顶金额
        $max = (float) ($actions['max'] ?? 0);
        if ($max > 0) {
            $discount = min($discount, $max);
        }

        return max(0.0, $discount);
    }

    /**
     * 选取命中的阶梯（min 不超过小计中的最大者）。
     *
     * @param  array  $tiers  [['min'=>..,'value'=>..], ...]
     * @param  float  $subtotal
     * @return array|null
     */
    private function resolveTier(array $tiers, float $subtotal): ?array
    {
        $matched = null;
        $bestMin = -1;

        foreach ($tiers as $tier) {
            $min = (float) ($tier['min'] ?? 0);
            if ($subtotal >= $min && $min > $bestMin) {
                $matched = $tier;
                $bestMin = $min;
            }
        }

        return $matched;
    }

    /**
     * 订单确认后落库促销应用流水，并并发安全地累加已用次数。
     *
     * 仅处理来源为 promotion 的折扣项（coupon 由 CouponService 负责）。
     *
     * @param  mixed  $order
     * @param  array  $appliedDiscounts
     * @return void
     */
    public function persistOrderLogs(mixed $order, array $appliedDiscounts): void
    {
        $repo = PromotionRepo::getInstance();

        foreach ($appliedDiscounts as $entry) {
            if (($entry['type'] ?? '') !== 'promotion') {
                continue;
            }

            PromotionOrderLog::query()->create([
                'order_id'        => $order->id,
                'promotion_id'    => $entry['promotion_id'] ?? null,
                'coupon_id'       => null,
                'code'            => $entry['code'] ?? '',
                'discount_amount' => $entry['amount'] ?? 0,
                'snapshot'        => $entry['snapshot'] ?? [],
            ]);

            if (! empty($entry['promotion_id'])) {
                $repo->incrementUsed((int) $entry['promotion_id']);
            }
        }
    }

    /**
     * 回滚某订单的促销已用次数并清理流水（订单取消/退款时调用）。
     *
     * @param  mixed  $order
     * @return void
     */
    public function rollback(mixed $order): void
    {
        $orderId = is_object($order) ? $order->id : (int) $order;
        $repo    = PromotionRepo::getInstance();

        $logs = PromotionOrderLog::query()
            ->where('order_id', $orderId)
            ->whereNotNull('promotion_id')
            ->get();

        foreach ($logs as $log) {
            $repo->decrementUsed((int) $log->promotion_id);
        }

        PromotionOrderLog::query()
            ->where('order_id', $orderId)
            ->whereNotNull('promotion_id')
            ->delete();
    }
}
