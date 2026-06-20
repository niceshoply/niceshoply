<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\VirtualGoods\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Order;
use Plugin\VirtualGoods\Models\VirtualCard;
use Plugin\VirtualGoods\Models\VirtualDelivery;
use Plugin\VirtualGoods\Models\VirtualGood;

class VirtualGoodsService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 批量导入卡密（按行）。返回导入条数。
     */
    public function importCards(string $productSku, string $raw): int
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $count = 0;

        foreach ($lines as $line) {
            $content = trim($line);
            if ($content === '') {
                continue;
            }
            VirtualCard::query()->create([
                'product_sku' => $productSku,
                'content'     => $content,
                'status'      => VirtualCard::STATUS_UNUSED,
            ]);
            $count++;
        }

        return $count;
    }

    public function unusedCount(string $productSku): int
    {
        return VirtualCard::query()
            ->where('product_sku', $productSku)
            ->where('status', VirtualCard::STATUS_UNUSED)
            ->count();
    }

    /**
     * 订单支付成功后自动发放虚拟商品（幂等）。
     */
    public function handleOrderPaid(?Order $order): void
    {
        if (! $order) {
            return;
        }

        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $sku = (string) $item->product_sku;
            if ($sku === '') {
                continue;
            }

            /** @var VirtualGood|null $vg */
            $vg = VirtualGood::query()->where('product_sku', $sku)->where('is_active', true)->first();
            if (! $vg) {
                continue;
            }

            try {
                $this->deliverItem($order, $item, $vg);
            } catch (\Throwable $e) {
                Log::error('virtual_goods.deliver.failed', [
                    'order_id' => $order->id, 'item_id' => $item->id, 'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function deliverItem(Order $order, $item, VirtualGood $vg): void
    {
        DB::transaction(function () use ($order, $item, $vg) {
            // 幂等：该订单项已发放则跳过
            $exists = VirtualDelivery::query()->where('order_item_id', $item->id)->lockForUpdate()->first();
            if ($exists) {
                return;
            }

            $qty     = max(1, (int) $item->quantity);
            $content = '';

            if ($vg->type === VirtualGood::TYPE_TEXT) {
                $content = (string) $vg->fixed_content;
            } else {
                $cards = VirtualCard::query()
                    ->where('product_sku', $vg->product_sku)
                    ->where('status', VirtualCard::STATUS_UNUSED)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->limit($qty)
                    ->get();

                if ($cards->isEmpty()) {
                    Log::warning('virtual_goods.out_of_stock', ['product_sku' => $vg->product_sku, 'order_id' => $order->id]);

                    return;
                }

                $contents = [];
                foreach ($cards as $card) {
                    $card->status        = VirtualCard::STATUS_USED;
                    $card->order_id      = $order->id;
                    $card->order_item_id = $item->id;
                    $card->customer_id   = $order->customer_id;
                    $card->used_at       = now();
                    $card->save();
                    $contents[] = $card->content;
                }
                $content = implode("\n", $contents);
            }

            VirtualDelivery::query()->create([
                'order_id'      => $order->id,
                'order_item_id' => $item->id,
                'customer_id'   => (int) $order->customer_id,
                'product_sku'   => $vg->product_sku,
                'content'       => $content,
            ]);

            $this->notify($order, $vg->name ?: $item->name, $content);
        });
    }

    protected function notify(Order $order, string $name, string $content): void
    {
        if ((int) $order->customer_id <= 0) {
            return;
        }
        if (! class_exists(\Plugin\NotifyCenter\Services\NotifyService::class)) {
            return;
        }

        try {
            \Plugin\NotifyCenter\Services\NotifyService::getInstance()->notify(
                (int) $order->customer_id,
                __('VirtualGoods::common.notify_title'),
                __('VirtualGoods::common.notify_body', ['name' => $name, 'number' => $order->number, 'content' => $content])
            );
        } catch (\Throwable $e) {
            Log::warning('virtual_goods.notify.failed', ['error' => $e->getMessage()]);
        }
    }
}
