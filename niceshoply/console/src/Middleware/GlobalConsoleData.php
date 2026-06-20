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
use NiceShoply\Common\Services\JwtTokenService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GlobalConsoleData
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
        $currentAdmin    = current_admin();
        $consoleApiToken = session('console_api_token');

        if ($currentAdmin) {
            $needNewToken = empty($consoleApiToken) || $this->isTokenExpired($consoleApiToken);

            if ($needNewToken) {
                $jwtService = app(JwtTokenService::class);
                $tokenData  = $jwtService->issueToken($currentAdmin, 'admin_api', 'web');
                session(['console_api_token' => $tokenData['access_token']]);
            }
        }

        view()->share('current_console_locale', current_console_locale());
        view()->share('admin', $currentAdmin);

        return $next($request);
    }

    /**
     * Check if a JWT token is expired or will expire soon.
     */
    private function isTokenExpired(string $token): bool
    {
        try {
            $payload   = JWTAuth::setToken($token)->getPayload();
            $exp       = $payload->get('exp');
            $bufferSec = 120; // refresh 2 minutes before expiry

            return $exp <= (time() + $bufferSec);
        } catch (Exception) {
            return true;
        }
    }
}
