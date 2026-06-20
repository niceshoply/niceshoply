<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StorePickup\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\StorePickup\Models\PickupStore;

class StoreController extends BaseController
{
    protected string $modelClass = PickupStore::class;

    public function index(): mixed
    {
        $stores = PickupStore::query()->orderBy('sort')->orderByDesc('id')->paginate(20);

        return nice_view('StorePickup::console.index', compact('stores'));
    }

    public function store(Request $request): mixed
    {
        try {
            PickupStore::query()->create($this->validateData($request));

            return json_success(__('StorePickup::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            PickupStore::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('StorePickup::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            PickupStore::query()->findOrFail($id)->delete();

            return json_success(__('StorePickup::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:128',
            'phone'          => 'nullable|string|max:32',
            'address'        => 'nullable|string|max:255',
            'business_hours' => 'nullable|string|max:128',
            'is_active'      => 'nullable|boolean',
            'sort'           => 'nullable|integer|min:0',
        ]);
    }
}
