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
use NiceShoply\Common\Services\JwtTokenService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GlobalFrontData
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
        $customer = current_customer();
        $favTotal = $customer ? $customer->favorites->count() : 0;

        $frontApiToken = session('front_api_token');

        if ($customer) {
            $needNewToken = empty($frontApiToken) || $this->isTokenExpired($frontApiToken);

            if ($needNewToken) {
                $jwtService = app(JwtTokenService::class);
                $tokenData  = $jwtService->issueToken($customer, 'customer_api', 'web');
                session(['front_api_token' => $tokenData['access_token']]);
            }
        }

        view()->share('current_locale', current_locale());
        view()->share('customer', $customer);
        view()->share('fav_total', $favTotal);

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
