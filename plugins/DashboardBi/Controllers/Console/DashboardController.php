<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\DashboardBi\Controllers\Console;

use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\DashboardBi\Services\DashboardService;

class DashboardController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('DashboardBi::console.index');
    }

    public function data(Request $request): mixed
    {
        $days = (int) $request->input('days', 30);

        return json_success('ok', DashboardService::getInstance()->summary($days));
    }
}
