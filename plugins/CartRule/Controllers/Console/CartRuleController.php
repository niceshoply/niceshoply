<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRule\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\CartRule\Models\CartRule;

class CartRuleController extends BaseController
{
    protected string $modelClass = CartRule::class;

    public function index(): mixed
    {
        $rules = CartRule::query()->orderBy('min_amount')->paginate(20);

        return nice_view('CartRule::console.index', compact('rules'));
    }

    public function create(): mixed
    {
        $rule = new CartRule(['discount_type' => 'fixed', 'active' => true]);

        return nice_view('CartRule::console.form', compact('rule'));
    }

    public function store(Request $request): mixed
    {
        try {
            CartRule::query()->create($this->validateData($request));

            return json_success(__('CartRule::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $rule = CartRule::query()->findOrFail($id);

        return nice_view('CartRule::console.form', compact('rule'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            CartRule::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('CartRule::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            CartRule::query()->findOrFail($id)->delete();

            return json_success(__('CartRule::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'           => 'nullable|string|max:191',
            'min_amount'     => 'required|numeric|min:0',
            'discount_type'  => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'start_at'       => 'nullable|date',
            'end_at'         => 'nullable|date|after_or_equal:start_at',
            'active'         => 'nullable|boolean',
        ]);
    }
}
