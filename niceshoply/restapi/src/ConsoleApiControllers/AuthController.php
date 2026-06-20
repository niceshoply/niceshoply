<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use NiceShoply\Common\Services\JwtTokenService;

class AuthController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function login(Request $request): mixed
    {
        try {
            if (! Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
                throw ValidationException::withMessages(['email' => [__('auth.credentials_error')]]);
            }

            $admin      = Auth::guard('admin')->user();
            $jwtService = app(JwtTokenService::class);
            $tokenData  = $jwtService->issueToken($admin, 'admin_api', $request->header('User-Agent'));

            return create_json_success($tokenData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Refresh the JWT token.
     *
     * @return mixed
     */
    public function refresh(): mixed
    {
        try {
            $jwtService = app(JwtTokenService::class);
            $tokenData  = $jwtService->refreshToken('admin_api');

            return create_json_success($tokenData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Logout - invalidate the current token.
     *
     * @return mixed
     */
    public function logout(): mixed
    {
        try {
            $jwtService = app(JwtTokenService::class);
            $jwtService->revokeCurrentToken('admin_api');

            return create_json_success(['message' => __('auth.logout_success')]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function admin(Request $request): mixed
    {
        $user = $request->user();

        return read_json_success($user);
    }
}
