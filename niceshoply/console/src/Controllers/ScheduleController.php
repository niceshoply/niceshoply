<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Services\Ops\ScheduleMonitorService;

/**
 * 计划任务可视化与手动触发。
 */
class ScheduleController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('console::schedule.index', [
            'tasks' => ScheduleMonitorService::getInstance()->listTasks(),
        ]);
    }

    public function run(Request $request): mixed
    {
        $command = (string) $request->input('command', '');
        $result  = ScheduleMonitorService::getInstance()->runManually($command);

        if (! $result['success']) {
            return json_fail($result['error'] ?: trans('console/schedule.run_failed'));
        }

        return json_success(trans('console/schedule.run_success'), [
            'output' => $result['output'],
        ]);
    }
}
