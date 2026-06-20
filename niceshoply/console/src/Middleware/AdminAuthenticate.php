<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Middleware;

use Closure;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use NiceShoply\Console\Repositories\RouteRepo;

class AdminAuthenticate extends Middleware
{
    /**
     * @param  $request
     * @param  Closure  $next
     * @param  ...$guards
     * @return mixed
     * @throws AuthenticationException
     * @throws Exception
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $this->authenticate($request, $guards);

        $routeName = $request->route()->getName();
        $routeCode = str_replace('console.', '', $routeName);
        if (in_array($routeCode, RouteRepo::IGNORE_LIST)) {
            return $next($request);
        }

        $routeCode = str_replace('.', '_', $routeCode);
        if (! current_admin()->can($routeCode)) {
            app()->setLocale(console_locale_code());

            return response()->view('console::errors.403', [], 403);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|void
     */
    protected function redirectTo(Request $request)
    {
        if (! $request->expectsJson()) {
            session(['console_redirect_uri' => $request->getUri()]);

            return console_route('login.index');
        }
    }
}
