<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\RiskControl\Services;

use Plugin\RiskControl\Models\Blacklist;
use Plugin\RiskControl\Models\RiskEvent;

class RiskControlService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('risk_control', 'enabled', true);
    }

    public function isBlacklisted(string $type, ?string $value): bool
    {
        if (! $value) {
            return false;
        }

        return Blacklist::query()->where('type', $type)->where('value', $value)->exists();
    }

    public function log(string $scene, string $level, string $rule, ?string $ip, ?string $subject, ?string $detail = null): RiskEvent
    {
        return RiskEvent::query()->create([
            'scene'   => $scene,
            'level'   => $level,
            'rule'    => $rule,
            'ip'      => $ip,
            'subject' => $subject,
            'detail'  => $detail,
        ]);
    }

    /**
     * 注册场景风控评估。
     */
    public function evaluateRegister(?string $email, ?string $ip): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ($this->isBlacklisted('email', $email)) {
            $this->log('register', 'high', 'blacklist_email', $ip, $email, 'email in blacklist');
        }
        if ($this->isBlacklisted('ip', $ip)) {
            $this->log('register', 'high', 'blacklist_ip', $ip, $email, 'ip in blacklist');
        }

        $limit = (int) plugin_setting('risk_control', 'ip_register_limit', 5);
        if ($limit > 0 && $ip) {
            $count = RiskEvent::query()
                ->where('scene', 'register')
                ->where('rule', 'register_attempt')
                ->where('ip', $ip)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            $this->log('register', 'low', 'register_attempt', $ip, $email);

            if ($count + 1 > $limit) {
                $this->log('register', 'high', 'ip_register_flood', $ip, $email, "registrations in 1h exceed {$limit}");
            }
        }
    }

    /**
     * 下单场景风控评估。
     */
    public function evaluateOrder($order, ?string $ip): void
    {
        if (! $this->enabled() || ! $order) {
            return;
        }

        $email = $order->customer_email ?? null;
        $phone = $order->shipping_telephone ?? null;
        $no    = $order->number ?? null;

        if ($this->isBlacklisted('email', $email)) {
            $this->log('order', 'high', 'blacklist_email', $ip, $no, 'order email in blacklist');
        }
        if ($this->isBlacklisted('phone', $phone)) {
            $this->log('order', 'high', 'blacklist_phone', $ip, $no, 'order phone in blacklist');
        }
        if ($this->isBlacklisted('ip', $ip)) {
            $this->log('order', 'high', 'blacklist_ip', $ip, $no, 'order ip in blacklist');
        }

        $limit = (int) plugin_setting('risk_control', 'ip_order_limit', 10);
        if ($limit > 0 && $ip) {
            $count = RiskEvent::query()
                ->where('scene', 'order')
                ->where('rule', 'order_attempt')
                ->where('ip', $ip)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            $this->log('order', 'low', 'order_attempt', $ip, $no);

            if ($count + 1 > $limit) {
                $this->log('order', 'high', 'ip_order_flood', $ip, $no, "orders in 1h exceed {$limit}");
            }
        }
    }
}
