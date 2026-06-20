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
use Illuminate\Http\RedirectResponse;
use NiceShoply\Common\Repositories\CustomerRepo;
use NiceShoply\Front\Requests\PasswordRequest;

class PasswordController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [];

        return nice_view('account/password', $data);
    }

    /**
     * Request to change password.
     *
     * @param  PasswordRequest  $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(PasswordRequest $request): RedirectResponse
    {
        try {
            CustomerRepo::getInstance()->updatePassword(current_customer(), $request->all());

            return redirect(account_route('password.index'))
                ->with('success', front_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(account_route('password.index'))
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
