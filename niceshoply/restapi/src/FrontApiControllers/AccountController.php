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
use NiceShoply\Common\Models\CustomerDeviceToken;
use NiceShoply\Common\Models\PointLog;
use NiceShoply\Common\Repositories\CustomerRepo;
use NiceShoply\Common\Resources\CustomerDetail;
use NiceShoply\Common\Services\Compliance\GdprService;
use NiceShoply\Common\Services\Member\PointService;
use NiceShoply\Front\Requests\PasswordRequest;
use NiceShoply\Front\Requests\SetPasswordRequest;

class AccountController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function me(Request $request): mixed
    {
        $user   = $request->user();
        $result = new CustomerDetail($user);

        return read_json_success($result);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function updateProfile(Request $request): mixed
    {
        try {
            $customer    = $request->user();
            $requestData = $request->only(['avatar', 'name', 'email']);
            CustomerRepo::getInstance()->update($customer, $requestData);

            $result = new CustomerDetail($customer);

            return update_json_success($result);

        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Request to change password.
     *
     * @param  PasswordRequest  $request
     * @return mixed
     */
    public function updatePassword(PasswordRequest $request): mixed
    {
        try {
            $customer = $request->user();
            CustomerRepo::getInstance()->updatePassword($customer, $request->all());
            $result = new CustomerDetail($customer);

            return update_json_success($result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Request to change password.
     *
     * @param  SetPasswordRequest  $request
     * @return mixed
     */
    public function setPassword(SetPasswordRequest $request): mixed
    {
        try {
            $customer = $request->user();
            if ($customer->has_password) {
                throw new Exception('Has set password, should use API: PUT /account/password');
            }

            CustomerRepo::getInstance()->forceUpdatePassword($customer, $request->get('new_password'));
            $result = new CustomerDetail($customer);

            return update_json_success($result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 积分概览（余额与规则）
     */
    public function points(Request $request): mixed
    {
        $customerId = (int) $request->user()->id;
        $service    = PointService::getInstance();

        return read_json_success([
            'enabled'            => $service->isEnabled(),
            'balance'            => $service->getBalance($customerId),
            'redeem_rate'        => $service->getRedeemRate(),
            'max_redeem_percent' => $service->getMaxRedeemPercent(),
        ]);
    }

    /**
     * 当前客户积分流水
     */
    public function pointLogs(Request $request): mixed
    {
        $customerId = (int) $request->user()->id;

        $logs = PointLog::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 15));

        return read_json_success($logs);
    }

    /**
     * 注册 App 推送 Token
     */
    public function registerDeviceToken(Request $request): mixed
    {
        try {
            $request->validate([
                'token'    => 'required|string|max:512',
                'platform' => 'nullable|string|max:32',
            ]);

            $customer = $request->user();

            CustomerDeviceToken::query()->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'token'       => $request->get('token'),
                ],
                [
                    'platform' => (string) $request->get('platform', ''),
                ]
            );

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 申请 GDPR 数据导出
     */
    public function requestDataExport(Request $request): mixed
    {
        try {
            $customer = $request->user();
            GdprService::getInstance()->requestExport($customer, (string) $request->ip());

            return create_json_success([
                'message' => trans('front/privacy.export_requested'),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 申请账户删除（匿名化）
     */
    public function requestAccountDelete(Request $request): mixed
    {
        try {
            $customer = $request->user();
            GdprService::getInstance()->requestDelete($customer, (string) $request->ip());

            return create_json_success([
                'message' => trans('front/privacy.delete_requested'),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
