<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\PointsMall\Models\MallItem;

class ItemController extends BaseController
{
    protected string $modelClass = MallItem::class;

    public function index(): mixed
    {
        $items = MallItem::query()->orderBy('sort')->orderByDesc('id')->paginate(20);

        return nice_view('PointsMall::console.items', compact('items'));
    }

    public function create(): mixed
    {
        $item = new MallItem(['type' => 'goods', 'is_active' => true, 'stock' => 0]);

        return nice_view('PointsMall::console.item_form', compact('item'));
    }

    public function store(Request $request): mixed
    {
        try {
            MallItem::query()->create($this->validateData($request));

            return json_success(__('PointsMall::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $item = MallItem::query()->findOrFail($id);

        return nice_view('PointsMall::console.item_form', compact('item'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            MallItem::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('PointsMall::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            MallItem::query()->findOrFail($id)->delete();

            return json_success(__('PointsMall::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:191',
            'image'       => 'nullable|string|max:500',
            'type'        => 'required|in:goods,coupon',
            'ref_id'      => 'nullable|integer|min:0',
            'points_cost' => 'required|integer|min:0',
            'cash_cost'   => 'nullable|numeric|min:0',
            'stock'       => 'required|integer',
            'per_limit'   => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
            'sort'        => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:2000',
        ]);
    }
}
