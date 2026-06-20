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
use NiceShoply\Console\Repositories\BaseRepo;

class CustomerRepo extends BaseRepo
{
    /**
     * @return array
     */
    public function getCustomerCountLatestWeek(): array
    {
        $filters = [
            'start' => today()->subWeek(),
            'end'   => today()->endOfDay(),
        ];
        $articleTotals = \NiceShoply\Common\Repositories\CustomerRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as total'))
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
     * Get customer source data
     *
     * @return array
     */
    public function getCustomerSourceData(): array
    {
        $sourceData = \NiceShoply\Common\Repositories\CustomerRepo::getInstance()->builder()
            ->select(DB::raw('`from`, count(*) as total'))
            ->groupBy('from')
            ->get()
            ->keyBy('from');

        $labels      = [];
        $data        = [];
        $fromOptions = \NiceShoply\Common\Repositories\CustomerRepo::getFromList();

        // Iterate through all possible source types
        foreach ($fromOptions as $option) {
            $key   = $option['key'];
            $label = $option['value'];

            // Basic data
            $total    = isset($sourceData[$key]) ? $sourceData[$key]->total : 0;
            $labels[] = $label;
            $data[]   = $total;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}
