<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Referral\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugin\Referral\Models\ReferralBinding;
use Plugin\Referral\Models\ReferralCode;
use Plugin\Referral\Models\ReferralReward;

class ReferralService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('referral', 'enabled', true);
    }

    /**
     * 获取或创建会员邀请码。
     */
    public function codeFor(int $customerId): string
    {
        if ($customerId <= 0) {
            return '';
        }

        $row = ReferralCode::query()->where('customer_id', $customerId)->first();
        if ($row) {
            return $row->code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (ReferralCode::query()->where('code', $code)->exists());

        ReferralCode::query()->create(['customer_id' => $customerId, 'code' => $code]);

        return $code;
    }

    public function inviteUrl(string $code): string
    {
        return url('/register?ref='.$code);
    }

    /**
     * 注册时绑定邀请关系并发放注册奖励。
     */
    public function onRegister($customer): void
    {
        if (! $this->enabled() || ! $customer) {
            return;
        }

        $inviteeId = (int) ($customer->id ?? 0);
        if ($inviteeId <= 0) {
            return;
        }

        $ref = strtoupper(trim((string) (request()->input('ref') ?? request()->cookie('ref') ?? '')));
        if ($ref === '') {
            return;
        }

        $inviter = ReferralCode::query()->where('code', $ref)->first();
        if (! $inviter || (int) $inviter->customer_id === $inviteeId) {
            return;
        }

        if (ReferralBinding::query()->where('invitee_id', $inviteeId)->exists()) {
            return;
        }

        ReferralBinding::query()->create([
            'inviter_id' => $inviter->customer_id,
            'invitee_id' => $inviteeId,
            'code'       => $ref,
            'bound_at'   => now(),
        ]);

        $this->grantRegisterRewards((int) $inviter->customer_id, $inviteeId);
    }

    protected function grantRegisterRewards(int $inviterId, int $inviteeId): void
    {
        $inviterPts = (int) plugin_setting('referral', 'inviter_points', 100);
        $inviteePts = (int) plugin_setting('referral', 'invitee_points', 50);

        if ($inviterPts > 0) {
            $this->addPoints($inviterId, $inviterPts);
            $this->logReward($inviterId, $inviteeId, 'register', 'points', (string) $inviterPts);
        }
        if ($inviteePts > 0) {
            $this->addPoints($inviteeId, $inviteePts);
            $this->logReward($inviterId, $inviteeId, 'register_invitee', 'points', (string) $inviteePts);
        }

        $couponId = (int) plugin_setting('referral', 'inviter_coupon_id', 0);
        if ($couponId > 0 && Schema::hasTable('coupons')) {
            $coupon = DB::table('coupons')->where('id', $couponId)->where('active', 1)->first();
            if ($coupon && Schema::hasTable('coupon_claims')) {
                DB::table('coupon_claims')->insert([
                    'coupon_id'   => $coupon->id,
                    'customer_id' => $inviterId,
                    'code'        => $coupon->code,
                    'claimed_at'  => now(),
                ]);
                $this->logReward($inviterId, $inviteeId, 'register', 'coupon', $coupon->code);
            }
        }
    }

    /**
     * 被邀请人首单支付成功后奖励邀请人。
     */
    public function onFirstOrderPaid($order): void
    {
        if (! $this->enabled() || ! $order) {
            return;
        }

        $inviteeId = (int) ($order->customer_id ?? 0);
        if ($inviteeId <= 0) {
            return;
        }

        $binding = ReferralBinding::query()->where('invitee_id', $inviteeId)->first();
        if (! $binding) {
            return;
        }

        if (ReferralReward::query()->where('invitee_id', $inviteeId)->where('scene', 'first_order')->exists()) {
            return;
        }

        $pts = (int) plugin_setting('referral', 'inviter_order_points', 200);
        if ($pts > 0) {
            $this->addPoints((int) $binding->inviter_id, $pts);
            $this->logReward((int) $binding->inviter_id, $inviteeId, 'first_order', 'points', (string) $pts);
        }
    }

    protected function addPoints(int $customerId, int $points): void
    {
        $svc = '\Plugin\Points\Services\PointService';
        if ($points > 0 && class_exists($svc)) {
            $svc::getInstance()->change($customerId, $points, 'referral', 0, __('Referral::common.points_remark'));
        }
    }

    protected function logReward(int $inviterId, int $inviteeId, string $scene, string $type, string $value): void
    {
        ReferralReward::query()->create([
            'inviter_id'   => $inviterId,
            'invitee_id'   => $inviteeId,
            'scene'        => $scene,
            'reward_type'  => $type,
            'reward_value' => $value,
            'created_at'   => now(),
        ]);
    }

    public function stats(int $customerId): array
    {
        $invited = ReferralBinding::query()->where('inviter_id', $customerId)->count();
        $rewards = ReferralReward::query()->where('inviter_id', $customerId)->where('reward_type', 'points')
            ->get()->sum(fn ($r) => (int) $r->reward_value);

        return [
            'code'          => $this->codeFor($customerId),
            'invite_url'    => $this->inviteUrl($this->codeFor($customerId)),
            'invited_count' => $invited,
            'points_earned' => (int) $rewards,
        ];
    }
}
