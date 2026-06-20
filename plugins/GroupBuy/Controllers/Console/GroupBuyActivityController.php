<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\GroupBuy\Models\GroupBuyActivity;
use Plugin\GroupBuy\Models\GroupBuyGroup;

class GroupBuyActivityController extends BaseController
{
    protected string $modelClass = GroupBuyActivity::class;

    public function index(): mixed
    {
        $activities = GroupBuyActivity::query()->orderByDesc('id')->paginate(20);

        return nice_view('GroupBuy::console.index', compact('activities'));
    }

    public function create(): mixed
    {
        $activity = new GroupBuyActivity(['active' => true, 'group_size' => 2, 'time_limit_minutes' => 1440]);

        return nice_view('GroupBuy::console.form', compact('activity'));
    }

    public function store(Request $request): mixed
    {
        try {
            GroupBuyActivity::query()->create($this->validateData($request));

            return json_success(__('GroupBuy::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $activity = GroupBuyActivity::query()->findOrFail($id);

        return nice_view('GroupBuy::console.form', compact('activity'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            GroupBuyActivity::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('GroupBuy::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            GroupBuyActivity::query()->findOrFail($id)->delete();

            return json_success(__('GroupBuy::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function groups(int $id): mixed
    {
        $activity = GroupBuyActivity::query()->findOrFail($id);
        $groups   = GroupBuyGroup::query()->where('activity_id', $id)->orderByDesc('id')->paginate(20);

        return nice_view('GroupBuy::console.groups', compact('activity', 'groups'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'               => 'required|string|max:128',
            'sku_id'             => 'required|integer|min:1',
            'product_id'         => 'nullable|integer|min:0',
            'group_price'        => 'required|numeric|min:0',
            'group_size'         => 'required|integer|min:2',
            'time_limit_minutes' => 'required|integer|min:1',
            'start_at'           => 'nullable|date',
            'end_at'             => 'nullable|date|after_or_equal:start_at',
            'active'             => 'nullable|boolean',
        ]);
    }
}
