<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Requests\LoginRequest;

class LoginController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        if (auth('admin')->check()) {
            return redirect()->back();
        }

        if ($request->has('locale')) {
            session(['console_locale' => $request->get('locale')]);

            return redirect(console_route('login.index'));
        }

        return nice_view('console::login');
    }

    /**
     * Login post request
     *
     * @param  LoginRequest  $request
     * @return mixed
     * @throws Exception
     */
    public function store(LoginRequest $request): mixed
    {
        $redirectUri = session('console_redirect_uri');
        if (auth('admin')->attempt($request->validated())) {
            if ($redirectUri) {
                session()->forget('console_redirect_uri');

                return redirect()->to($redirectUri);
            }

            return redirect(console_route('home.index'));
        }

        return redirect()->back()
            ->with('instance', auth('admin')->user())
            ->with(['error' => trans('auth.failed')])->withInput();
    }
}
