<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CouponCenter\Controllers\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\CouponCenter\Models\CouponClaim;

class CouponCenterController extends BaseController
{
    protected string $modelClass = CouponClaim::class;

    public function index(): mixed
    {
        $hasCoupons = Schema::hasTable('coupons');
        $totalClaims = CouponClaim::query()->count();

        $stats = collect();
        if ($hasCoupons) {
            $claimRows = CouponClaim::query()
                ->select('coupon_id', DB::raw('COUNT(*) as claims'))
                ->groupBy('coupon_id')
                ->orderByDesc('claims')
                ->limit(100)
                ->get();

            $coupons = DB::table('coupons')->whereIn('id', $claimRows->pluck('coupon_id')->all())->get()->keyBy('id');

            $stats = $claimRows->map(fn ($r) => [
                'name'   => optional($coupons->get($r->coupon_id))->name ?? ('#'.$r->coupon_id),
                'code'   => optional($coupons->get($r->coupon_id))->code ?? '',
                'claims' => (int) $r->claims,
            ]);
        }

        return nice_view('CouponCenter::console.index', compact('hasCoupons', 'totalClaims', 'stats'));
    }
}
