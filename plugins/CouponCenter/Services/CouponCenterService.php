<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CouponCenter\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugin\CouponCenter\Models\CouponClaim;

class CouponCenterService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('coupon_center', 'enabled', true) && Schema::hasTable('coupons');
    }

    protected function claimLimit(): int
    {
        return max(1, (int) plugin_setting('coupon_center', 'claim_limit', 1));
    }

    /**
     * 可领取的券列表（启用、在有效期内、未领完）。
     */
    public function claimable(int $customerId = 0): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $now = Carbon::now();
        $rows = DB::table('coupons')
            ->where('active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->get();

        $claimedIds = $customerId > 0
            ? CouponClaim::query()->where('customer_id', $customerId)->pluck('coupon_id')->all()
            : [];

        return $rows->map(fn ($c) => $this->present($c, in_array($c->id, $claimedIds)))->all();
    }

    /**
     * 领取一张券。
     *
     * @throws Exception
     */
    public function claim(int $customerId, int $couponId): array
    {
        if ($customerId <= 0) {
            throw new Exception(__('CouponCenter::common.need_login'));
        }
        if (! $this->enabled()) {
            throw new Exception(__('CouponCenter::common.disabled'));
        }

        $coupon = DB::table('coupons')->where('id', $couponId)->where('active', 1)->first();
        if (! $coupon) {
            throw new Exception(__('CouponCenter::common.invalid'));
        }

        $now = Carbon::now();
        if ($coupon->end_at && $now->gt(Carbon::parse($coupon->end_at))) {
            throw new Exception(__('CouponCenter::common.expired'));
        }

        $claimed = CouponClaim::query()
            ->where('coupon_id', $couponId)
            ->where('customer_id', $customerId)
            ->count();
        if ($claimed >= $this->claimLimit()) {
            throw new Exception(__('CouponCenter::common.already_claimed'));
        }

        CouponClaim::query()->create([
            'coupon_id'   => $couponId,
            'customer_id' => $customerId,
            'code'        => $coupon->code,
            'claimed_at'  => $now,
        ]);

        return ['code' => $coupon->code];
    }

    /**
     * 我的券包（含使用状态）。
     */
    public function mine(int $customerId): array
    {
        if ($customerId <= 0 || ! $this->enabled()) {
            return [];
        }

        $claims = CouponClaim::query()->where('customer_id', $customerId)->orderByDesc('id')->get();
        if ($claims->isEmpty()) {
            return [];
        }

        $coupons = DB::table('coupons')->whereIn('id', $claims->pluck('coupon_id')->all())->get()->keyBy('id');

        // 已使用次数
        $usedCounts = [];
        if (Schema::hasTable('coupon_usages')) {
            $usedCounts = DB::table('coupon_usages')
                ->where('customer_id', $customerId)
                ->whereIn('code', $claims->pluck('code')->all())
                ->select('code', DB::raw('COUNT(*) as c'))
                ->groupBy('code')
                ->pluck('c', 'code')
                ->all();
        }

        return $claims->map(function ($claim) use ($coupons, $usedCounts) {
            $c = $coupons->get($claim->coupon_id);
            if (! $c) {
                return null;
            }
            $data = $this->present($c, true);
            $data['claimed_at'] = (string) $claim->claimed_at;
            $data['used'] = (int) ($usedCounts[$claim->code] ?? 0) > 0;

            return $data;
        })->filter()->values()->all();
    }

    protected function present(object $c, bool $claimed): array
    {
        return [
            'id'           => $c->id,
            'code'         => $c->code,
            'name'         => $c->name,
            'type'         => $c->type,
            'value'        => (float) $c->value,
            'min_amount'   => (float) $c->min_amount,
            'min_amount_format' => currency_format((float) $c->min_amount),
            'max_discount' => (float) $c->max_discount,
            'start_at'     => (string) $c->start_at,
            'end_at'       => (string) $c->end_at,
            'claimed'      => $claimed,
        ];
    }
}
