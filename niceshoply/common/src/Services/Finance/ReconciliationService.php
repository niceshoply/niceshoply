<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Finance;

use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Payment;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Services\StateMachineService;

/**
 * 财务对账服务。
 *
 * 按周期汇总：已支付订单收入 − 成功退款 − 支付手续费 = 净收入。
 */
final class ReconciliationService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 汇总指定周期的对账数据。
     *
     * @param  string|\DateTimeInterface  $start  起始时间（含）
     * @param  string|\DateTimeInterface  $end  结束时间（含）
     * @return array{period: array, income: float, refunds: float, fees: float, net: float, order_count: int, refund_count: int}
     */
    public function summarize(string|\DateTimeInterface $start, string|\DateTimeInterface $end): array
    {
        $startAt = Carbon::parse($start)->startOfDay();
        $endAt   = Carbon::parse($end)->endOfDay();

        $paidStatuses = StateMachineService::getValidStatuses();

        $orderQuery = Order::query()
            ->whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$startAt, $endAt]);

        $income = (float) (clone $orderQuery)->sum('total');

        $refundQuery = Refund::query()
            ->where('status', 'succeeded')
            ->whereBetween('processed_at', [$startAt, $endAt]);

        $refunds = (float) (clone $refundQuery)->sum('amount');

        $fees = (float) Payment::query()
            ->where('paid', true)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->sum('handling_fee');

        $income  = round($income, 4);
        $refunds = round($refunds, 4);
        $fees    = round($fees, 4);
        $net     = round($income - $refunds - $fees, 4);

        $summary = [
            'period' => [
                'start' => $startAt->toDateTimeString(),
                'end'   => $endAt->toDateTimeString(),
            ],
            'income'       => $income,
            'refunds'      => $refunds,
            'fees'         => $fees,
            'net'          => $net,
            'order_count'  => (clone $orderQuery)->count(),
            'refund_count' => (clone $refundQuery)->count(),
        ];

        return fire_hook_filter('service.reconciliation.summarize', $summary);
    }

    /**
     * 按日拆分对账明细（用于报表与导出）。
     *
     * @param  string|\DateTimeInterface  $start
     * @param  string|\DateTimeInterface  $end
     * @return array<int, array{date: string, income: float, refunds: float, fees: float, net: float}>
     */
    public function dailyBreakdown(string|\DateTimeInterface $start, string|\DateTimeInterface $end): array
    {
        $startAt = Carbon::parse($start)->startOfDay();
        $endAt   = Carbon::parse($end)->endOfDay();
        $period  = Carbon::parse($startAt)->daysUntil($endAt);

        $paidStatuses = StateMachineService::getValidStatuses();

        $incomeByDay = Order::query()
            ->whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $refundsByDay = Refund::query()
            ->where('status', 'succeeded')
            ->whereBetween('processed_at', [$startAt, $endAt])
            ->selectRaw('DATE(processed_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $feesByDay = Payment::query()
            ->where('paid', true)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->selectRaw('DATE(created_at) as day, SUM(handling_fee) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $rows = [];
        foreach ($period as $day) {
            $date    = $day->toDateString();
            $income  = round((float) ($incomeByDay[$date] ?? 0), 4);
            $refunds = round((float) ($refundsByDay[$date] ?? 0), 4);
            $fees    = round((float) ($feesByDay[$date] ?? 0), 4);

            $rows[] = [
                'date'    => $date,
                'income'  => $income,
                'refunds' => $refunds,
                'fees'    => $fees,
                'net'     => round($income - $refunds - $fees, 4),
            ];
        }

        return fire_hook_filter('service.reconciliation.daily_breakdown', $rows);
    }
}
