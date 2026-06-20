<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Support\Facades\Auth;
use NiceShoply\Common\Services\JwtTokenService;

class LogoutController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $admin = Auth::guard('admin')->user();

        // 注销该管理员的所有 JWT Token
        if ($admin) {
            app(JwtTokenService::class)->revokeAllTokens($admin);
        }

        Auth::guard('admin')->logout();
        session()->forget('console_api_token');

        return redirect(console_route('login.index'))
            ->with('instance', $admin);
    }
}
