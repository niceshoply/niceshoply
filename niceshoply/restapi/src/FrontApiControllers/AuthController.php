<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use NiceShoply\Common\Services\JwtTokenService;
use NiceShoply\Front\Requests\ForgottenRequest;
use NiceShoply\Front\Requests\LoginRequest;
use NiceShoply\Front\Requests\RegisterRequest;
use NiceShoply\Front\Requests\VerifyCodeRequest;
use NiceShoply\Front\Services\AccountService;
use Throwable;

class AuthController extends BaseController
{
    /**
     * @param  RegisterRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function register(RegisterRequest $request): mixed
    {
        try {
            $data = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Register by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                $customer = AccountService::getInstance()->registerBySms($data);
                auth('customer')->login($customer);
            } else {
                // Register by email and password
                $credentials = $request->only('email', 'password');
                $customer    = AccountService::getInstance()->register($credentials);
                auth('customer')->attempt($credentials);
            }

            $jwtService = app(JwtTokenService::class);
            $tokenData  = $jwtService->issueToken($customer, 'customer_api', $request->header('User-Agent'));

            return create_json_success($tokenData);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  LoginRequest  $request
     * @return mixed
     */
    public function login(LoginRequest $request): mixed
    {
        try {
            $data = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Login by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                $customer = AccountService::getInstance()->loginBySms($data);
                auth('customer')->login($customer);
            } else {
                // Login by email and password
                $credentials = $request->only('email', 'password');
                if (! auth('customer')->attempt($credentials)) {
                    throw ValidationException::withMessages(['email' => [__('auth.credentials_error')]]);
                }
            }

            $customer   = auth('customer')->user();
            $jwtService = app(JwtTokenService::class);
            $tokenData  = $jwtService->issueToken($customer, 'customer_api', $request->header('User-Agent'));

            return create_json_success($tokenData);
        } catch (Exception $e) {
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
            $tokenData  = $jwtService->refreshToken('customer_api');

            return create_json_success($tokenData);
        } catch (Exception $e) {
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
            $jwtService->revokeCurrentToken('customer_api');

            return create_json_success(['message' => __('auth.logout_success')]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Send SMS verification code
     *
     * @param  Request  $request
     * @return mixed
     */
    public function sendSmsCode(Request $request): mixed
    {
        try {
            $request->validate([
                'calling_code' => 'required|string|max:10',
                'telephone'    => 'required|string|max:20',
                'type'         => 'required|in:register,login',
            ]);

            AccountService::getInstance()->sendSmsCode(
                $request->input('calling_code'),
                $request->input('telephone'),
                $request->input('type')
            );

            return create_json_success(['message' => 'SMS code sent successfully']);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Translate error message with specific error details (same format as backend test)
            $translatedMessage = __('common/sms.send_failed', ['message' => $errorMessage]);

            return json_fail($translatedMessage);
        }
    }

    /**
     * Send email verification code for password reset.
     *
     * @param  VerifyCodeRequest  $request
     * @return mixed
     */
    public function sendVerifyCode(VerifyCodeRequest $request): mixed
    {
        try {
            $email = $request->get('email');
            AccountService::getInstance()->sendVerifyCode($email);

            return json_success(trans('front/forgotten.verification_code_sent'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Reset password with verification code.
     *
     * @param  ForgottenRequest  $request
     * @return mixed
     */
    public function resetPassword(ForgottenRequest $request): mixed
    {
        try {
            $code     = $request->get('code');
            $email    = $request->get('email');
            $password = $request->get('password');

            AccountService::getInstance()->verifyUpdatePassword($code, $email, $password);

            return json_success(trans('front/forgotten.password_updated'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
