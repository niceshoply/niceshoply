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
use Illuminate\Support\Facades\Auth;
use NiceShoply\Common\Services\JwtTokenService;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * JWT 认证中间件: 解析 Bearer Token，自动识别 guard 并认证用户。
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $forceGuard  可选: 强制指定 guard (admin_api / customer_api)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $forceGuard = null): mixed
    {
        try {
            // 解析 token 并获取 payload
            $rawToken = JWTAuth::parseToken();
            $payload  = $rawToken->getPayload();

            // 从 token 中获取 guard claim，或使用强制指定的 guard
            $guard = $forceGuard ?? $payload->get('guard');

            if (! $guard || ! in_array($guard, ['admin_api', 'customer_api'])) {
                return $this->unauthorized(__('auth.token_guard_invalid'));
            }

            // 使用对应 guard 认证用户
            /** @var JWTGuard $jwtGuard */
            $jwtGuard = Auth::guard($guard);
            $jwtGuard->setToken($rawToken->getToken()->get());
            $user = $jwtGuard->user();

            if (! $user) {
                return $this->unauthorized(__('auth.user_not_found'));
            }

            // 设置当前 guard
            Auth::shouldUse($guard);

            // 将 user 设置到 request 上，兼容 request()->user()
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // 更新最后使用时间
            $tokenId = $payload->get('jti');
            if ($tokenId) {
                app(JwtTokenService::class)->touchToken($tokenId);
            }

        } catch (TokenExpiredException $e) {
            return $this->unauthorized(__('auth.token_expired'), 401);
        } catch (TokenInvalidException $e) {
            return $this->unauthorized(__('auth.token_invalid'), 401);
        } catch (JWTException $e) {
            return $this->unauthorized(__('auth.token_missing'), 401);
        }

        return $next($request);
    }

    /**
     * 返回未授权响应。
     */
    private function unauthorized(string $message, int $code = 401): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
