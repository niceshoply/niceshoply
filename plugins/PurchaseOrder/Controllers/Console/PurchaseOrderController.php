<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PurchaseOrder\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\PurchaseOrder\Models\PurchaseOrder;
use Plugin\PurchaseOrder\Models\Supplier;
use Plugin\PurchaseOrder\Services\PurchaseOrderService;

class PurchaseOrderController extends BaseController
{
    protected string $modelClass = PurchaseOrder::class;

    public function index(): mixed
    {
        $suppliers  = Supplier::query()->orderByDesc('id')->get();
        $orders     = PurchaseOrder::query()->with('items')->orderByDesc('id')->limit(30)->get();
        $suggestions = PurchaseOrderService::getInstance()->lowStockSuggestions();

        return nice_view('PurchaseOrder::console.index', compact('suppliers', 'orders', 'suggestions'));
    }

    public function storeSupplier(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'name'    => 'required|string|max:100',
                'contact' => 'nullable|string|max:64',
                'phone'   => 'nullable|string|max:32',
                'email'   => 'nullable|email|max:128',
            ]);
            Supplier::query()->create($data + ['is_active' => true]);

            return json_success(__('PurchaseOrder::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function storeOrder(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'supplier_id'  => 'required|integer',
                'warehouse_id' => 'nullable|integer',
                'items'        => 'required|string', // sku_id:qty:cost, ...
                'remark'       => 'nullable|string|max:255',
            ]);

            $items = [];
            foreach (preg_split('/[,，]+/', $data['items']) as $pair) {
                $parts = array_map('trim', explode(':', $pair));
                if (count($parts) >= 2) {
                    $items[] = [
                        'sku_id'     => (int) $parts[0],
                        'quantity'   => (int) $parts[1],
                        'cost_price' => (float) ($parts[2] ?? 0),
                    ];
                }
            }
            if (empty($items)) {
                return json_fail(__('PurchaseOrder::common.invalid_items'));
            }

            $po = PurchaseOrderService::getInstance()->create(
                (int) $data['supplier_id'],
                $items,
                (int) ($data['warehouse_id'] ?? 0),
                $data['remark'] ?? ''
            );

            return json_success(__('PurchaseOrder::common.created', ['no' => $po->po_number]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function receive(int $id): mixed
    {
        try {
            PurchaseOrderService::getInstance()->receive($id);

            return json_success(__('PurchaseOrder::common.received'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
