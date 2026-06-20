<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlashSale\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\FlashSale\Models\FlashSale;
use Plugin\FlashSale\Models\FlashSaleItem;

class FlashSaleController extends BaseController
{
    protected string $modelClass = FlashSale::class;

    public function index(): mixed
    {
        $sales = FlashSale::query()->withCount('items')->orderByDesc('id')->paginate(20);

        return nice_view('FlashSale::console.index', compact('sales'));
    }

    public function create(): mixed
    {
        $sale = new FlashSale(['active' => true]);

        return nice_view('FlashSale::console.form', compact('sale'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $this->validateData($request);

            DB::transaction(function () use ($data, $request) {
                $sale = FlashSale::query()->create($data);
                $this->syncItems($sale, $request->input('items', []));
            });

            return json_success(__('FlashSale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $sale = FlashSale::query()->with('items')->findOrFail($id);

        return nice_view('FlashSale::console.form', compact('sale'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            $data = $this->validateData($request);

            DB::transaction(function () use ($data, $request, $id) {
                $sale = FlashSale::query()->findOrFail($id);
                $sale->update($data);
                $sale->items()->delete();
                $this->syncItems($sale, $request->input('items', []));
            });

            return json_success(__('FlashSale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            $sale = FlashSale::query()->findOrFail($id);
            $sale->items()->delete();
            $sale->delete();

            return json_success(__('FlashSale::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'     => 'required|string|max:128',
            'start_at' => 'nullable|date',
            'end_at'   => 'nullable|date|after_or_equal:start_at',
            'active'   => 'nullable|boolean',
        ]);
    }

    private function syncItems(FlashSale $sale, array $items): void
    {
        foreach ($items as $item) {
            $skuId     = (int) ($item['sku_id'] ?? 0);
            $salePrice = (float) ($item['sale_price'] ?? 0);
            if ($skuId <= 0 || $salePrice < 0) {
                continue;
            }
            FlashSaleItem::query()->create([
                'flash_sale_id' => $sale->id,
                'sku_id'        => $skuId,
                'product_id'    => (int) ($item['product_id'] ?? 0),
                'sale_price'    => $salePrice,
                'qty_limit'     => (int) ($item['qty_limit'] ?? 0),
                'sold'          => 0,
            ]);
        }
    }
}
