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
use NiceShoply\Common\Exceptions\Unauthorized;
use NiceShoply\Common\Services\CartService;
use NiceShoply\Common\Services\Checkout\BillingService;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Member\PointService;
use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Front\Requests\CheckoutConfirmRequest;
use Throwable;

class CheckoutController extends BaseController
{
    /**
     * 附加积分上下文，供 App 结账页展示
     *
     * @throws Throwable
     */
    private function enrichCheckoutResult(array $result, CheckoutService $checkout): array
    {
        $customerId  = token_customer_id();
        $pointService = PointService::getInstance();

        $result['points'] = [
            'enabled'            => $pointService->isEnabled(),
            'balance'            => $customerId > 0 ? $pointService->getBalance($customerId) : 0,
            'applied'            => $checkout->getAppliedPoints(),
            'redeem_rate'        => $pointService->getRedeemRate(),
            'max_redeem_percent' => $pointService->getMaxRedeemPercent(),
        ];

        return $result;
    }

    /**
     * Get checkout data and render page.
     *
     * @return mixed
     * @throws Throwable
     */
    public function index(): mixed
    {
        try {
            $checkout = CheckoutService::getInstance(token_customer_id());
            $result   = $this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout);

            return read_json_success($result);
        } catch (Unauthorized $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Update checkout, include shipping address, shipping method, billing address, billing method
     *
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function update(Request $request): mixed
    {
        try {
            $data     = $request->all();
            $checkout = CheckoutService::getInstance(token_customer_id());
            $checkout->updateValues($data);
            $result = $this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout);

            return update_json_success($result);
        } catch (Unauthorized $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Confirm checkout and place order
     *
     * @param  CheckoutConfirmRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function confirm(CheckoutConfirmRequest $request): mixed
    {
        try {
            $data     = $request->all();
            $checkout = CheckoutService::getInstance(token_customer_id());
            if ($data) {
                $checkout->updateValues($data);
            }

            $order = $checkout->confirm();
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID, '', true);

            return submit_json_success($order);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 应用优惠券：校验券码并写入结账上下文，返回最新结账结果。
     *
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function applyCoupon(Request $request): mixed
    {
        try {
            $code     = (string) $request->input('code', '');
            $checkout = CheckoutService::getInstance(token_customer_id());

            $result = $checkout->applyCoupon($code);
            if (! $result['valid']) {
                return json_fail($result['message']);
            }

            return update_json_success($this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 移除已应用的优惠券。
     *
     * @return mixed
     * @throws Throwable
     */
    public function removeCoupon(): mixed
    {
        try {
            $checkout = CheckoutService::getInstance(token_customer_id());
            $checkout->removeCoupon();

            return update_json_success($this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 应用积分抵现
     */
    public function applyPoints(Request $request): mixed
    {
        try {
            $points   = (int) $request->input('points', 0);
            $checkout = CheckoutService::getInstance(token_customer_id());

            $result = $checkout->applyPoints($points);
            if (! $result['valid']) {
                return json_fail($result['message']);
            }

            return update_json_success($this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 移除积分抵现
     */
    public function removePoints(): mixed
    {
        try {
            $checkout = CheckoutService::getInstance(token_customer_id());
            $checkout->removePoints();

            return update_json_success($this->enrichCheckoutResult($checkout->getCheckoutResult(), $checkout));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function billingMethods(): mixed
    {
        try {
            $methods = BillingService::getInstance()->getMethods();

            return read_json_success($methods);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function quickConfirm(Request $request): mixed
    {
        try {
            CartService::getInstance(token_customer_id())->addCart($request->all());

            $checkoutService = CheckoutService::getInstance(token_customer_id());
            $checkoutData    = ['billing_method_code' => $request->get('shipping_method_code')];
            $checkoutService->updateValues($checkoutData);

            $order = $checkoutService->confirm();
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID, '', true);

            return submit_json_success($order);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
