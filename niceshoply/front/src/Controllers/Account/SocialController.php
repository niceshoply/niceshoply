<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use Exception;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use NiceShoply\Common\Repositories\Customer\SocialRepo;
use NiceShoply\Front\Controllers\BaseController;
use Throwable;

class SocialController extends BaseController
{
    /**
     * @param  string  $provider
     * @return RedirectResponse
     * @throws Exception
     */
    public function redirect(string $provider): RedirectResponse
    {
        SocialRepo::getInstance()->initSocialConfig();
        if ($provider == 'twitter') {
            $provider = 'twitter-oauth-2';
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * @param  string  $provider
     * @return mixed
     * @throws Throwable
     */
    public function callback(string $provider): mixed
    {

        try {
            SocialRepo::getInstance()->initSocialConfig();
            if ($provider == 'twitter') {
                $provider = 'twitter-oauth-2';
            }
            $user     = Socialite::driver($provider)->user();
            $userData = [
                'uid'    => $user->getId(),
                'email'  => $user->getEmail(),
                'name'   => $user->getName(),
                'avatar' => $user->getAvatar(),
                'token'  => $user->token,
                'raw'    => $user->getRaw(),
            ];
            $customer = SocialRepo::getInstance()->createCustomer($provider, $userData);
            auth('customer')->login($customer);

            return nice_view('account.social_callback');

        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
}
