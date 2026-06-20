<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BuyXGetY\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\BuyXGetY\Models\BxgyRule;

class BxgyController extends BaseController
{
    protected string $modelClass = BxgyRule::class;

    public function index(): mixed
    {
        $rules = BxgyRule::query()->orderByDesc('id')->get();

        return nice_view('BuyXGetY::console.index', compact('rules'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'               => 'nullable|integer',
                'name'             => 'required|string|max:100',
                'product_id'       => 'nullable|integer|min:0',
                'buy_qty'          => 'required|integer|min:1',
                'get_qty'          => 'required|integer|min:1',
                'discount_percent' => 'required|integer|min:1|max:100',
                'is_active'        => 'nullable|boolean',
            ]);

            BxgyRule::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'             => $data['name'],
                    'product_id'       => (int) ($data['product_id'] ?? 0),
                    'buy_qty'          => (int) $data['buy_qty'],
                    'get_qty'          => (int) $data['get_qty'],
                    'discount_percent' => (int) $data['discount_percent'],
                    'is_active'        => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('BuyXGetY::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        BxgyRule::query()->whereKey($id)->delete();

        return json_success(__('BuyXGetY::common.deleted'));
    }
}
