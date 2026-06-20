<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class MaintenanceMode
{
    /**
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (system_setting('maintenance_mode')) {
            if (current_admin()) {
                return $next($request);
            }

            $routeName = pure_route_name();

            if (! in_array($routeName, ['locales.switch', 'currencies.switch'])) {
                return response()->view('maintenance');
            }
        }

        return $next($request);
    }
}
