<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bundle\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Bundle\Models\BundleDeal;

class BundleController extends BaseController
{
    protected string $modelClass = BundleDeal::class;

    public function index(): mixed
    {
        $deals = BundleDeal::query()->orderByDesc('id')->get();

        return nice_view('Bundle::console.index', compact('deals'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'           => 'nullable|integer',
                'name'         => 'required|string|max:100',
                'items'        => 'required|string',     // "12:1, 15:2"
                'bundle_price' => 'required|numeric|min:0',
                'is_active'    => 'nullable|boolean',
            ]);

            $items = $this->parseItems($data['items']);
            if (empty($items)) {
                return json_fail(__('Bundle::common.invalid_items'));
            }

            BundleDeal::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'         => $data['name'],
                    'items'        => $items,
                    'bundle_price' => $data['bundle_price'],
                    'is_active'    => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('Bundle::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        BundleDeal::query()->whereKey($id)->delete();

        return json_success(__('Bundle::common.deleted'));
    }

    /**
     * 解析 "12:1, 15:2" 为 [['product_id'=>12,'quantity'=>1], ...]
     */
    protected function parseItems(string $raw): array
    {
        $items = [];
        foreach (preg_split('/[,，]+/', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            $parts = explode(':', $pair);
            $pid = (int) trim($parts[0] ?? 0);
            $qty = (int) trim($parts[1] ?? 1);
            if ($pid > 0) {
                $items[] = ['product_id' => $pid, 'quantity' => max(1, $qty)];
            }
        }

        return $items;
    }
}
