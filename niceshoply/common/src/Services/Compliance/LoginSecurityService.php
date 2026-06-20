<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Compliance;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\CustomerLoginLog;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\Notification\NotificationManager;

/**
 * 登录安全：日志记录、异常登录提醒、下单频控。
 */
class LoginSecurityService extends BaseService
{
    /**
     * 记录成功登录并检测是否新设备/IP。
     */
    public function recordSuccessfulLogin(Customer $customer, Request $request): CustomerLoginLog
    {
        $ip        = (string) $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 512);
        $isNew     = ! CustomerLoginLog::query()
            ->where('customer_id', $customer->id)
            ->where('success', true)
            ->where('ip', $ip)
            ->exists();

        $log = CustomerLoginLog::query()->create([
            'customer_id'   => $customer->id,
            'ip'            => $ip,
            'user_agent'    => $userAgent,
            'success'       => true,
            'is_new_device' => $isNew,
        ]);

        if ($isNew && system_setting('login_anomaly_alert_enabled', true)) {
            $this->notifyAnomalousLogin($customer, $ip, $userAgent);
        }

        return $log;
    }

    /**
     * 记录失败登录。
     */
    public function recordFailedLogin(?string $email, Request $request, string $reason = ''): void
    {
        CustomerLoginLog::query()->create([
            'customer_id'    => 0,
            'ip'             => (string) $request->ip(),
            'user_agent'     => substr((string) $request->userAgent(), 0, 512),
            'success'        => false,
            'failure_reason' => $reason.($email ? " ({$email})" : ''),
        ]);
    }

    /**
     * 下单频控：超出限制则抛异常。
     */
    public function assertOrderRateAllowed(int $customerId, string $ip): void
    {
        if (! system_setting('order_rate_limit_enabled', true)) {
            return;
        }

        $customerLimit = (int) system_setting('order_rate_limit_customer', 10);
        $ipLimit       = (int) system_setting('order_rate_limit_ip', 20);
        $windowMinutes = (int) system_setting('order_rate_limit_window_minutes', 60);
        $since         = Carbon::now()->subMinutes(max(1, $windowMinutes));

        if ($customerId > 0 && $customerLimit > 0) {
            $customerCount = Order::query()
                ->where('customer_id', $customerId)
                ->where('created_at', '>=', $since)
                ->count();

            if ($customerCount >= $customerLimit) {
                throw new Exception(trans('front/checkout.order_rate_limit'));
            }
        }

        if ($ip !== '' && $ipLimit > 0) {
            $ipCount = Order::query()
                ->where('ip', $ip)
                ->where('created_at', '>=', $since)
                ->count();

            if ($ipCount >= $ipLimit) {
                throw new Exception(trans('front/checkout.order_rate_limit'));
            }
        }
    }

    private function notifyAnomalousLogin(Customer $customer, string $ip, string $userAgent): void
    {
        if (NotificationManager::isEventEnabled('login_anomaly')) {
            NotificationManager::getInstance()->notify(
                '异常登录提醒',
                "> **客户:** {$customer->email}\n> **IP:** {$ip}\n> **UA:** {$userAgent}",
                'warning'
            );
        }

        fire_hook_action('service.login.anomaly', [
            'customer'   => $customer,
            'ip'         => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
