<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use NiceShoply\Common\Services\Ops\HealthCheckService;

/**
 * 系统健康自检页。
 */
class HealthCheckController extends BaseController
{
    public function index(): mixed
    {
        $service = HealthCheckService::getInstance();
        $checks  = $service->runAll();

        return nice_view('console::health.index', [
            'checks'    => $checks,
            'isHealthy' => $service->isHealthy($checks),
        ]);
    }
}
