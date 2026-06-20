<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\CouponUsage;
use NiceShoply\Common\Repositories\CouponRepo;

/**
 * 优惠券后台控制器（CRUD、批量生成、核销记录查看）。
 */
class CouponController extends BaseController
{
    /**
     * 优惠券列表。
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => CouponRepo::getCriteria(),
            'coupons'  => CouponRepo::getInstance()->list($filters),
        ];

        return nice_view('console::coupons.index', $data);
    }

    /**
     * 新建页面。
     *
     * @return mixed
     */
    public function create(): mixed
    {
        return $this->form(new Coupon);
    }

    /**
     * 保存新建（支持批量生成）。
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $attributes = $this->normalize($request);
            $batchCount = (int) $request->input('batch_count', 0);

            if ($batchCount > 1) {
                // 批量生成：忽略手填 code，按前缀随机生成
                unset($attributes['code']);
                CouponRepo::getInstance()->batchGenerate($attributes, $batchCount, $request->input('batch_prefix', ''));
            } else {
                CouponRepo::getInstance()->create($attributes);
            }

            return redirect(console_route('coupons.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('coupons.create'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 编辑页面。
     *
     * @param  Coupon  $coupon
     * @return mixed
     */
    public function edit(Coupon $coupon): mixed
    {
        return $this->form($coupon);
    }

    /**
     * 表单页面。
     *
     * @param  Coupon  $coupon
     * @return mixed
     */
    public function form(Coupon $coupon): mixed
    {
        $data = [
            'coupon'      => $coupon,
            'typeOptions' => CouponRepo::getTypeOptions(),
        ];

        return nice_view('console::coupons.form', $data);
    }

    /**
     * 保存编辑。
     *
     * @param  Request  $request
     * @param  Coupon  $coupon
     * @return RedirectResponse
     */
    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        try {
            CouponRepo::getInstance()->update($coupon, $this->normalize($request));

            return redirect(console_route('coupons.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('coupons.edit', [$coupon->id]))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 删除。
     *
     * @param  Coupon  $coupon
     * @return RedirectResponse
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        try {
            CouponRepo::getInstance()->destroy($coupon);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 查看某券的核销记录。
     *
     * @param  Coupon  $coupon
     * @return mixed
     */
    public function usages(Coupon $coupon): mixed
    {
        $data = [
            'coupon' => $coupon,
            'usages' => CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->orderByDesc('id')
                ->paginate(),
        ];

        return nice_view('console::coupons.usages', $data);
    }

    /**
     * 规整表单字段（统一券码大写，门槛/上限等数值化）。
     *
     * @param  Request  $request
     * @return array
     */
    private function normalize(Request $request): array
    {
        return [
            'code'               => strtoupper(trim((string) $request->input('code', ''))),
            'promotion_id'       => $request->input('promotion_id') ?: null,
            'type'               => $request->input('type', 'fixed'),
            'value'              => (float) $request->input('value', 0),
            'min_amount'         => (float) $request->input('min_amount', 0),
            'total_limit'        => (int) $request->input('total_limit', 0),
            'per_customer_limit' => (int) $request->input('per_customer_limit', 1),
            'starts_at'          => $request->input('starts_at') ?: null,
            'ends_at'            => $request->input('ends_at') ?: null,
            'active'             => (bool) $request->input('active', true),
        ];
    }
}
