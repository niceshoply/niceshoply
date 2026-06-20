<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Middleware;

use Closure;
use Illuminate\Http\Request;

class EventActionHook
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $hookName = $this->parseHookName($request);
        fire_hook_action($hookName.'.before', $request);

        $response = $next($request);

        $request->merge(['response' => $response]);
        fire_hook_action($hookName.'.after', $request);

        return $response;
    }

    /**
     * Parse hook name
     *
     * @param  Request  $request
     * @return string
     */
    private function parseHookName(Request $request): string
    {
        $route            = $request->route();
        $controllerAction = $route->getActionName();
        $controllerAction = str_replace(['NiceShoply\\', 'Controllers\\'], '', $controllerAction);
        $controllerAction = str_replace('Controller@', '\\', $controllerAction);
        $controllerAction = trim($controllerAction, '\\');

        return strtolower(str_replace('\\', '.', $controllerAction));
    }
}
