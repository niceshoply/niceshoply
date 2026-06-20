<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PurchaseOrder\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NiceShoply\Common\Models\Product\Sku;
use Plugin\PurchaseOrder\Models\PurchaseOrder;
use Plugin\PurchaseOrder\Models\PurchaseOrderItem;

class PurchaseOrderService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('purchase_order', 'enabled', true);
    }

    protected function threshold(): int
    {
        return max(0, (int) plugin_setting('purchase_order', 'low_stock_threshold', 10));
    }

    /**
     * 低库存补货建议。
     */
    public function lowStockSuggestions(): array
    {
        $threshold = $this->threshold();

        return Sku::query()
            ->where('quantity', '<=', $threshold)
            ->where('active', 1)
            ->orderBy('quantity')
            ->limit(100)
            ->get()
            ->map(fn ($s) => [
                'sku_id'   => $s->id,
                'sku_code' => $s->code,
                'quantity' => (int) $s->quantity,
                'suggest'  => max($threshold * 2 - (int) $s->quantity, 1),
            ])
            ->all();
    }

    /**
     * 创建采购单。
     *
     * @param  array<int,array{sku_id:int,quantity:int,cost_price?:float}>  $items
     */
    public function create(int $supplierId, array $items, int $warehouseId = 0, string $remark = ''): PurchaseOrder
    {
        $poNumber = 'PO'.date('Ymd').strtoupper(Str::random(4));
        $total    = 0;

        $po = PurchaseOrder::query()->create([
            'po_number'    => $poNumber,
            'supplier_id'  => $supplierId,
            'warehouse_id' => $warehouseId,
            'status'       => 'draft',
            'remark'       => $remark,
        ]);

        foreach ($items as $it) {
            $qty  = max(1, (int) ($it['quantity'] ?? 1));
            $cost = (float) ($it['cost_price'] ?? 0);
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $po->id,
                'sku_id'            => (int) $it['sku_id'],
                'quantity'          => $qty,
                'cost_price'        => $cost,
            ]);
            $total += $cost * $qty;
        }

        $po->update(['total' => $total]);

        return $po->load('items');
    }

    public function markOrdered(int $poId): void
    {
        PurchaseOrder::query()->whereKey($poId)->update(['status' => 'ordered', 'ordered_at' => now()]);
    }

    /**
     * 采购入库。
     *
     * @throws Exception
     */
    public function receive(int $poId): void
    {
        $po = PurchaseOrder::query()->with('items')->find($poId);
        if (! $po || $po->status === 'received') {
            throw new Exception(__('PurchaseOrder::common.invalid_po'));
        }

        DB::transaction(function () use ($po) {
            $whSvc = class_exists('\Plugin\MultiWarehouse\Services\WarehouseService')
                ? \Plugin\MultiWarehouse\Services\WarehouseService::getInstance()
                : null;

            foreach ($po->items as $item) {
                $qty = (int) $item->quantity - (int) $item->received_qty;
                if ($qty <= 0) {
                    continue;
                }

                if ($whSvc && $po->warehouse_id > 0) {
                    $existing = DB::table('warehouse_stocks')
                        ->where('warehouse_id', $po->warehouse_id)
                        ->where('sku_id', $item->sku_id)
                        ->value('quantity') ?? 0;
                    $whSvc->setStock((int) $po->warehouse_id, (int) $item->sku_id, (int) $existing + $qty);
                } else {
                    Sku::query()->whereKey($item->sku_id)->increment('quantity', $qty);
                }

                $item->update(['received_qty' => $item->quantity]);
            }

            $po->update(['status' => 'received', 'received_at' => now()]);
        });
    }
}
