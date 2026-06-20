<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\VirtualGoods\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\VirtualGoods\Models\VirtualCard;
use Plugin\VirtualGoods\Models\VirtualDelivery;
use Plugin\VirtualGoods\Models\VirtualGood;
use Plugin\VirtualGoods\Services\VirtualGoodsService;

class VirtualGoodsController extends BaseController
{
    protected string $modelClass = VirtualGood::class;

    public function index(): mixed
    {
        $threshold = (int) plugin_setting('virtual_goods', 'low_stock_threshold', 0);

        $goods = VirtualGood::query()->orderByDesc('id')->get()->map(function (VirtualGood $g) {
            $g->unused = VirtualGoodsService::getInstance()->unusedCount($g->product_sku);

            return $g;
        });

        return nice_view('VirtualGoods::console.index', compact('goods', 'threshold'));
    }

    public function store(Request $request): mixed
    {
        try {
            VirtualGood::query()->updateOrCreate(
                ['product_sku' => $request->input('product_sku')],
                $this->validateData($request)
            );

            return json_success(__('VirtualGoods::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            VirtualGood::query()->findOrFail($id)->delete();

            return json_success(__('VirtualGoods::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function importCards(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'product_sku' => 'required|string|max:64',
                'cards'       => 'required|string',
            ]);
            $count = VirtualGoodsService::getInstance()->importCards($data['product_sku'], $data['cards']);

            return json_success(__('VirtualGoods::common.imported', ['count' => $count]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function cards(Request $request): mixed
    {
        $sku   = (string) $request->query('product_sku', '');
        $cards = VirtualCard::query()
            ->when($sku !== '', fn ($q) => $q->where('product_sku', $sku))
            ->orderByDesc('id')
            ->paginate(30);

        return nice_view('VirtualGoods::console.cards', compact('cards', 'sku'));
    }

    public function deliveries(): mixed
    {
        $deliveries = VirtualDelivery::query()->orderByDesc('id')->paginate(30);

        return nice_view('VirtualGoods::console.deliveries', compact('deliveries'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'product_sku'   => 'required|string|max:64',
            'name'          => 'nullable|string|max:191',
            'type'          => 'required|in:card,text',
            'fixed_content' => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ]);
    }
}
