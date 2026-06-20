<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Services\Finance\ReconciliationService;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * 财务对账后台控制器。
 */
class ReconciliationController extends BaseController
{
    public function index(Request $request): mixed
    {
        $start = $request->input('start', now()->subDays(30)->toDateString());
        $end   = $request->input('end', now()->toDateString());

        $service = ReconciliationService::getInstance();
        $data    = [
            'start'     => $start,
            'end'       => $end,
            'summary'   => $service->summarize($start, $end),
            'breakdown' => $service->dailyBreakdown($start, $end),
        ];

        return nice_view('console::reconciliation.index', $data);
    }

    /**
     * 导出对账明细 Excel。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $start = $request->input('start', now()->subDays(30)->toDateString());
        $end   = $request->input('end', now()->toDateString());

        $rows = collect(ReconciliationService::getInstance()->dailyBreakdown($start, $end))
            ->map(fn (array $row) => [
                trans('console/reconciliation.date')    => $row['date'],
                trans('console/reconciliation.income')  => $row['income'],
                trans('console/reconciliation.refunds') => $row['refunds'],
                trans('console/reconciliation.fees')    => $row['fees'],
                trans('console/reconciliation.net')     => $row['net'],
            ]);

        return (new FastExcel($rows))->download('reconciliation_'.date('Ymd_His').'.xlsx');
    }
}
