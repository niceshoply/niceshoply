<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return read_json_success(Auth::guard('admin')->user());
    }
}
