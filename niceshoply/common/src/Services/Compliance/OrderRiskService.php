<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Compliance;

use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\Notification\NotificationManager;

/**
 * 订单风险评估服务。
 */
class OrderRiskService extends BaseService
{
    public const FLAG_HIGH_AMOUNT = 'high_amount';

    public const FLAG_ADDRESS_MISMATCH = 'address_mismatch';

    public const FLAG_HIGH_FREQUENCY = 'high_frequency';

    public const FLAG_NEW_CUSTOMER_HIGH_VALUE = 'new_customer_high_value';

    /**
     * 评估订单风险并写回 orders 表。
     */
    public function evaluateAndPersist(Order $order): array
    {
        $flags = [];
        $score = 0;

        $maxAmount = (float) system_setting('risk_order_max_amount', 10000);
        if ((float) $order->total >= $maxAmount) {
            $flags[] = self::FLAG_HIGH_AMOUNT;
            $score += 30;
        }

        if ($this->isAddressMismatch($order)) {
            $flags[] = self::FLAG_ADDRESS_MISMATCH;
            $score += 25;
        }

        $freqLimit = (int) system_setting('risk_order_frequency_limit', 5);
        $freqHours = (int) system_setting('risk_order_frequency_hours', 1);
        if ($this->exceedsOrderFrequency($order, $freqLimit, $freqHours)) {
            $flags[] = self::FLAG_HIGH_FREQUENCY;
            $score += 35;
        }

        $newCustomerHours = (int) system_setting('risk_new_customer_hours', 24);
        $newCustomerMin   = (float) system_setting('risk_new_customer_min_amount', 500);
        if ($this->isNewCustomerHighValue($order, $newCustomerHours, $newCustomerMin)) {
            $flags[] = self::FLAG_NEW_CUSTOMER_HIGH_VALUE;
            $score += 20;
        }

        $score       = min(100, $score);
        $highRisk    = $score >= (int) system_setting('risk_high_score_threshold', 50);
        $wasHighRisk = (bool) $order->is_high_risk;

        $order->risk_score   = $score;
        $order->risk_flags   = $flags;
        $order->is_high_risk = $highRisk;
        $order->save();

        if ($highRisk && ! $wasHighRisk && NotificationManager::isEventEnabled('high_risk_order')) {
            NotificationManager::getInstance()->notify(
                '高风险订单',
                "> **订单号:** {$order->number}\n> **金额:** {$order->total}\n> **风险分:** {$score}\n> **标记:** ".implode(', ', $flags),
                'warning'
            );
        }

        fire_hook_action('service.order.risk.evaluated', [
            'order'     => $order,
            'flags'     => $flags,
            'score'     => $score,
            'high_risk' => $highRisk,
        ]);

        return ['flags' => $flags, 'score' => $score, 'high_risk' => $highRisk];
    }

    private function isAddressMismatch(Order $order): bool
    {
        $shipCountry = trim((string) $order->shipping_country);
        $billCountry = trim((string) $order->billing_country);

        if ($shipCountry === '' || $billCountry === '') {
            return false;
        }

        return strcasecmp($shipCountry, $billCountry) !== 0;
    }

    private function exceedsOrderFrequency(Order $order, int $limit, int $hours): bool
    {
        if ($order->customer_id <= 0) {
            return false;
        }

        $since = Carbon::now()->subHours(max(1, $hours));

        $count = Order::query()
            ->where('customer_id', $order->customer_id)
            ->where('id', '!=', $order->id)
            ->where('created_at', '>=', $since)
            ->count();

        return $count >= $limit;
    }

    private function isNewCustomerHighValue(Order $order, int $hours, float $minAmount): bool
    {
        if ($order->customer_id <= 0 || (float) $order->total < $minAmount) {
            return false;
        }

        $customer = $order->customer;
        if (! $customer || ! $customer->created_at) {
            return false;
        }

        return $customer->created_at->gte(Carbon::now()->subHours(max(1, $hours)));
    }
}
