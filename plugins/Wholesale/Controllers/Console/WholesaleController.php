<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Wholesale\Models\WholesaleTier;

class WholesaleController extends BaseController
{
    protected string $modelClass = WholesaleTier::class;

    public function index(): mixed
    {
        $tiers = WholesaleTier::query()->orderBy('sku_id')->orderBy('min_qty')->paginate(50);

        return nice_view('Wholesale::console.index', compact('tiers'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'product_sku' => 'required|string|max:64',
                'min_qty'     => 'required|integer|min:1',
                'price'       => 'required|numeric|min:0',
                'is_active'   => 'nullable|boolean',
            ]);

            /** @var Sku|null $sku */
            $sku = Sku::query()->where('code', $data['product_sku'])->first();
            if (! $sku) {
                return json_fail(__('Wholesale::common.sku_not_found'));
            }

            WholesaleTier::query()->updateOrCreate(
                ['sku_id' => $sku->id, 'min_qty' => $data['min_qty']],
                [
                    'product_sku' => $sku->code,
                    'price'       => $data['price'],
                    'is_active'   => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('Wholesale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            WholesaleTier::query()->findOrFail($id)->delete();

            return json_success(__('Wholesale::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
