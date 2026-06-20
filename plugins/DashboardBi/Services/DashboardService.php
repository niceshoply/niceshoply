<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\DashboardBi\Services;

use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Services\StateMachineService;

class DashboardService
{
    /** 计入营收的订单状态 */
    protected const REVENUE_STATUSES = [
        StateMachineService::PAID,
        StateMachineService::COMPLETED,
    ];

    public static function getInstance(): static
    {
        return new static;
    }

    public function summary(int $days = 30): array
    {
        $days  = max(1, min($days, 365));
        $start = Carbon::today()->subDays($days - 1);

        $orders = Order::query()->where('created_at', '>=', $start);
        $orderCount = (clone $orders)->count();

        $revenueQuery = (clone $orders)->whereIn('status', self::REVENUE_STATUSES);
        $paidCount    = (clone $revenueQuery)->count();
        $revenue      = (float) (clone $revenueQuery)->sum('total');
        $aov          = $paidCount > 0 ? round($revenue / $paidCount, 2) : 0.0;

        $newCustomers = Customer::query()->where('created_at', '>=', $start)->count();

        return [
            'days'          => $days,
            'revenue'       => round($revenue, 2),
            'revenue_format' => currency_format($revenue),
            'order_count'   => $orderCount,
            'paid_count'    => $paidCount,
            'aov'           => $aov,
            'aov_format'    => currency_format($aov),
            'new_customers' => $newCustomers,
            'trend'         => $this->trend($start, $days),
            'status_dist'   => $this->statusDistribution($start),
            'top_products'  => $this->topProducts($start),
        ];
    }

    protected function trend(Carbon $start, int $days): array
    {
        // 初始化日期序列
        $labels  = [];
        $revMap  = [];
        $ordMap  = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->toDateString();
            $labels[] = $d;
            $revMap[$d] = 0;
            $ordMap[$d] = 0;
        }

        $rows = Order::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt, SUM(CASE WHEN status IN (?, ?) THEN total ELSE 0 END) as rev', self::REVENUE_STATUSES)
            ->groupBy('d')
            ->get();

        foreach ($rows as $row) {
            $d = (string) $row->d;
            if (isset($ordMap[$d])) {
                $ordMap[$d] = (int) $row->cnt;
                $revMap[$d] = round((float) $row->rev, 2);
            }
        }

        return [
            'labels'  => $labels,
            'revenue' => array_values($revMap),
            'orders'  => array_values($ordMap),
        ];
    }

    protected function statusDistribution(Carbon $start): array
    {
        $rows = Order::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return $rows->toArray();
    }

    protected function topProducts(Carbon $start, int $limit = 10): array
    {
        return Item::query()
            ->whereHas('order', function ($q) use ($start) {
                $q->where('created_at', '>=', $start)
                    ->whereIn('status', self::REVENUE_STATUSES);
            })
            ->selectRaw('name, SUM(quantity) as qty, SUM(quantity * price) as amount')
            ->groupBy('name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name'   => $r->name,
                'qty'    => (int) $r->qty,
                'amount' => round((float) $r->amount, 2),
            ])
            ->toArray();
    }
}
