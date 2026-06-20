<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance\Services;

use Plugin\FreightInsurance\Models\InsuranceRecord;

class FreightInsuranceService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('freight_insurance', 'enabled', true);
    }

    /**
     * 计算保费。
     */
    public function computePremium(float $subtotal): float
    {
        $mode  = (string) plugin_setting('freight_insurance', 'mode', 'fixed');
        $value = (float) plugin_setting('freight_insurance', 'value', 0);

        if ($mode === 'percent') {
            $premium = $subtotal * $value / 100;
            $min     = (float) plugin_setting('freight_insurance', 'min_premium', 0);
            $max     = (float) plugin_setting('freight_insurance', 'max_premium', 0);
            if ($min > 0) {
                $premium = max($premium, $min);
            }
            if ($max > 0) {
                $premium = min($premium, $max);
            }
        } else {
            $premium = $value;
        }

        return round(max($premium, 0), 2);
    }

    /**
     * 购物车小计。
     */
    public function cartSubtotal(array $cartList): float
    {
        $subtotal = 0;
        foreach ($cartList as $item) {
            if (! ($item['selected'] ?? true)) {
                continue;
            }
            $subtotal += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
        }

        return round($subtotal, 2);
    }

    /**
     * 下单后记录投保订单（幂等）。
     */
    public function recordOrder($order): void
    {
        if (! $order) {
            return;
        }
        // 仅当订单含运费险费用项时记录
        $premium = 0;
        foreach (($order->fees ?? []) as $fee) {
            if (($fee->code ?? '') === 'freight_insurance') {
                $premium = (float) $fee->total;
                break;
            }
        }
        if ($premium <= 0) {
            return;
        }

        InsuranceRecord::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'order_number' => $order->number,
                'customer_id'  => (int) $order->customer_id,
                'premium'      => $premium,
                'status'       => 'insured',
            ]
        );
    }
}
