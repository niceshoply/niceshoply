<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Presale\Models\PresaleActivity;
use Plugin\Presale\Models\PresaleItem;

class PresaleController extends BaseController
{
    protected string $modelClass = PresaleActivity::class;

    public function index(): mixed
    {
        $activities = PresaleActivity::query()->withCount('items')->orderByDesc('id')->paginate(20);

        return nice_view('Presale::console.index', compact('activities'));
    }

    public function create(): mixed
    {
        $activity = new PresaleActivity(['active' => true]);

        return nice_view('Presale::console.form', compact('activity'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $this->validateData($request);
            DB::transaction(function () use ($data, $request) {
                $activity = PresaleActivity::query()->create($data);
                $this->syncItems($activity, $request->input('items', []));
            });

            return json_success(__('Presale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $activity = PresaleActivity::query()->with('items')->findOrFail($id);

        return nice_view('Presale::console.form', compact('activity'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            $data = $this->validateData($request);
            DB::transaction(function () use ($data, $request, $id) {
                $activity = PresaleActivity::query()->findOrFail($id);
                $activity->update($data);
                $activity->items()->delete();
                $this->syncItems($activity, $request->input('items', []));
            });

            return json_success(__('Presale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            $activity = PresaleActivity::query()->findOrFail($id);
            $activity->items()->delete();
            $activity->delete();

            return json_success(__('Presale::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|string|max:128',
            'start_at'  => 'nullable|date',
            'end_at'    => 'nullable|date|after_or_equal:start_at',
            'ship_date' => 'nullable|date',
            'active'    => 'nullable|boolean',
        ]);
    }

    private function syncItems(PresaleActivity $activity, array $items): void
    {
        foreach ($items as $item) {
            $skuId = (int) ($item['sku_id'] ?? 0);
            if ($skuId <= 0) {
                continue;
            }
            PresaleItem::query()->create([
                'presale_id'    => $activity->id,
                'sku_id'        => $skuId,
                'product_id'    => (int) ($item['product_id'] ?? 0),
                'presale_price' => (float) ($item['presale_price'] ?? 0),
                'deposit'       => (float) ($item['deposit'] ?? 0),
                'expand'        => (float) ($item['expand'] ?? 0),
                'qty_limit'     => (int) ($item['qty_limit'] ?? 0),
                'sold'          => 0,
            ]);
        }
    }
}
