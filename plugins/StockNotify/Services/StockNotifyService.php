<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StockNotify\Services;

use Carbon\Carbon;
use RuntimeException;
use NiceShoply\Common\Models\Product\Sku;
use Plugin\NotifyCenter\Services\NotifyService;
use Plugin\StockNotify\Models\StockNotification;

class StockNotifyService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 登记提醒。同一会员+SKU+类型仅保留一条 pending。
     */
    public function subscribe(int $customerId, string $skuCode, string $type = 'restock', float $targetPrice = 0, int $productId = 0): StockNotification
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('StockNotify::common.need_login'));
        }
        $type = $type === 'price_drop' ? 'price_drop' : 'restock';

        return StockNotification::query()->updateOrCreate(
            [
                'customer_id' => $customerId,
                'sku_code'    => $skuCode,
                'type'        => $type,
                'status'      => 'pending',
            ],
            [
                'product_id'   => $productId,
                'target_price' => $type === 'price_drop' ? $targetPrice : 0,
                'notified_at'  => null,
            ]
        );
    }

    public function cancel(int $customerId, int $id): void
    {
        StockNotification::query()
            ->where('customer_id', $customerId)
            ->where('id', $id)
            ->update(['status' => 'cancelled']);
    }

    public function listForCustomer(int $customerId)
    {
        return StockNotification::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * 扫描待提醒并发送。返回已发送条数。
     */
    public function scanAndNotify(): int
    {
        if (! class_exists(NotifyService::class)) {
            return 0;
        }

        $pending = StockNotification::query()->where('status', 'pending')->get();
        $sent    = 0;

        foreach ($pending as $sub) {
            $sku = Sku::query()->where('code', $sub->sku_code)->first();
            if (! $sku) {
                continue;
            }

            $hit = match ($sub->type) {
                'price_drop' => $sub->target_price > 0 && (float) $sku->getFinalPrice() <= (float) $sub->target_price,
                default      => (int) $sku->quantity > 0,
            };

            if (! $hit) {
                continue;
            }

            $this->notify($sub, $sku);
            $sub->status      = 'notified';
            $sub->notified_at = Carbon::now();
            $sub->save();
            $sent++;
        }

        return $sent;
    }

    protected function notify(StockNotification $sub, Sku $sku): void
    {
        if ($sub->type === 'price_drop') {
            $title   = __('StockNotify::common.price_drop_title');
            $content = __('StockNotify::common.price_drop_content', [
                'sku'   => $sub->sku_code,
                'price' => currency_format($sku->getFinalPrice()),
            ]);
        } else {
            $title   = __('StockNotify::common.restock_title');
            $content = __('StockNotify::common.restock_content', ['sku' => $sub->sku_code]);
        }

        NotifyService::getInstance()->notify(
            (int) $sub->customer_id,
            $title,
            $content,
            'system',
            (int) $sub->product_id
        );
    }
}
