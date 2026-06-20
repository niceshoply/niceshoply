<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use NiceShoply\Common\Services\JwtTokenService;

class LogoutController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $customer = Auth::guard('customer')->user();

        // 注销该客户的所有 JWT Token
        if ($customer) {
            app(JwtTokenService::class)->revokeAllTokens($customer);
        }

        Auth::guard('customer')->logout();
        session()->forget('front_api_token');

        return redirect(front_route('home.index'));
    }
}
