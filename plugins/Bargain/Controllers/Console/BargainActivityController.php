<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Bargain\Models\BargainActivity;

class BargainActivityController extends BaseController
{
    protected string $modelClass = BargainActivity::class;

    public function index(): mixed
    {
        $activities = BargainActivity::query()->orderByDesc('id')->paginate(20);

        return nice_view('Bargain::console.index', compact('activities'));
    }

    public function create(): mixed
    {
        $activity = new BargainActivity([
            'active'             => true,
            'min_cut'            => 0.01,
            'max_cut'            => 1,
            'time_limit_minutes' => 1440,
        ]);

        return nice_view('Bargain::console.form', compact('activity'));
    }

    public function store(Request $request): mixed
    {
        try {
            BargainActivity::query()->create($this->validateData($request));

            return json_success(__('Bargain::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $activity = BargainActivity::query()->findOrFail($id);

        return nice_view('Bargain::console.form', compact('activity'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            BargainActivity::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('Bargain::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            BargainActivity::query()->findOrFail($id)->delete();

            return json_success(__('Bargain::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'               => 'required|string|max:128',
            'sku_id'             => 'required|integer|min:1',
            'product_id'         => 'nullable|integer|min:0',
            'origin_price'       => 'nullable|numeric|min:0',
            'floor_price'        => 'required|numeric|min:0',
            'min_cut'            => 'required|numeric|min:0.01',
            'max_cut'            => 'required|numeric|min:0.01|gte:min_cut',
            'time_limit_minutes' => 'required|integer|min:1',
            'start_at'           => 'nullable|date',
            'end_at'             => 'nullable|date|after_or_equal:start_at',
            'active'             => 'nullable|boolean',
        ]);
    }
}
