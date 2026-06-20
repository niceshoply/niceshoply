<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Coupon\Models\Coupon;

class CouponController extends BaseController
{
    protected string $modelClass = Coupon::class;

    public function index(Request $request): mixed
    {
        $coupons = Coupon::query()
            ->when($request->get('keyword'), function ($q, $keyword) {
                $q->where('code', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%");
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('Coupon::console.index', compact('coupons'));
    }

    public function create(): mixed
    {
        $coupon = new Coupon([
            'type'               => 'fixed',
            'per_customer_limit' => 1,
            'active'             => true,
        ]);

        return nice_view('Coupon::console.form', compact('coupon'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $this->validateData($request);
            Coupon::query()->create($data);

            return json_success(__('Coupon::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $coupon = Coupon::query()->findOrFail($id);

        return nice_view('Coupon::console.form', compact('coupon'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            $coupon = Coupon::query()->findOrFail($id);
            $data   = $this->validateData($request, $id);
            $coupon->update($data);

            return json_success(__('Coupon::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            Coupon::query()->findOrFail($id)->delete();

            return json_success(__('Coupon::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code'               => 'required|string|max:64|unique:coupons,code'.($id ? ",{$id}" : ''),
            'name'               => 'nullable|string|max:191',
            'type'               => 'required|in:fixed,percent,free_shipping',
            'value'              => 'required|numeric|min:0',
            'min_amount'         => 'nullable|numeric|min:0',
            'max_discount'       => 'nullable|numeric|min:0',
            'usage_limit'        => 'nullable|integer|min:0',
            'per_customer_limit' => 'nullable|integer|min:0',
            'start_at'           => 'nullable|date',
            'end_at'             => 'nullable|date|after_or_equal:start_at',
            'active'             => 'nullable|boolean',
        ]);
    }
}
