<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Repositories\Analytics;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Console\Repositories\BaseRepo;

class OrderRepo extends BaseRepo
{
    /**
     * @return array
     */
    public function getOrderTotalLatestWeek(): array
    {
        $filters = [
            'start'    => today()->subWeek(),
            'end'      => today()->endOfDay(),
            'statuses' => StateMachineService::getValidStatuses(),
        ];
        $articleTotals = \NiceShoply\Common\Repositories\OrderRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, sum(total) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create(today()->subWeek(), today())->toArray();
        foreach ($period as $date) {
            $dateFormat   = $date->format('Y-m-d');
            $articleTotal = $articleTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $totals[] = $articleTotal ? $articleTotal->total : 0;
        }

        return [
            'period' => $dates,
            'totals' => $totals,
        ];
    }

    /**
     * @return array
     */
    public function getOrderTotalLatestMonth(): array
    {
        $filters = [
            'start'    => today()->subMonth(),
            'end'      => today()->endOfDay(),
            'statuses' => StateMachineService::getValidStatuses(),
        ];
        $articleTotals = \NiceShoply\Common\Repositories\OrderRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, sum(total) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create(today()->subMonth(), today())->toArray();
        foreach ($period as $date) {
            $dateFormat   = $date->format('Y-m-d');
            $articleTotal = $articleTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $totals[] = $articleTotal ? $articleTotal->total : 0;
        }

        return [
            'period' => $dates,
            'totals' => $totals,
        ];
    }
}
