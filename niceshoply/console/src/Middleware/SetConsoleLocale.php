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
use Illuminate\Http\Request;

class SetConsoleLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $frontLocales = locales();
        $frontLocale  = front_locale_code();

        $consoleLocales = console_locales();
        $currentLocale  = console_locale_code();

        if (collect($consoleLocales)->contains('code', $currentLocale)) {
            if (! $frontLocales->contains('code', $currentLocale)) {
                session(['locale' => $frontLocale]);
            }
            app()->setLocale($currentLocale);
        } else {
            session(['locale' => $frontLocale]);
            app()->setLocale($frontLocale);
        }

        return $next($request);
    }
}
