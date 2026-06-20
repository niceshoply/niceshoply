<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall\Services;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Plugin\Points\Services\PointService;
use Plugin\PointsMall\Models\MallItem;
use Plugin\PointsMall\Models\Redemption;

class PointsMallService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 上架兑换商品分页。
     */
    public function listActive(int $perPage = 20)
    {
        return MallItem::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 兑换。校验积分余额、库存、限兑，扣分并生成兑换单。
     *
     * @throws RuntimeException
     */
    public function redeem(int $customerId, int $itemId, int $quantity = 1, string $contact = ''): Redemption
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('PointsMall::common.need_login'));
        }
        $quantity = max(1, $quantity);

        if (! class_exists(PointService::class)) {
            throw new RuntimeException(__('PointsMall::common.points_unavailable'));
        }

        return DB::transaction(function () use ($customerId, $itemId, $quantity, $contact) {
            /** @var MallItem $item */
            $item = MallItem::query()->lockForUpdate()->findOrFail($itemId);

            if (! $item->is_active) {
                throw new RuntimeException(__('PointsMall::common.item_off'));
            }
            if ($item->stock >= 0 && $item->stock < $quantity) {
                throw new RuntimeException(__('PointsMall::common.out_of_stock'));
            }

            // 每人限兑校验
            if ($item->per_limit > 0) {
                $already = (int) Redemption::query()
                    ->where('item_id', $item->id)
                    ->where('customer_id', $customerId)
                    ->where('status', '!=', 'cancelled')
                    ->sum('quantity');
                if ($already + $quantity > $item->per_limit) {
                    throw new RuntimeException(__('PointsMall::common.over_limit'));
                }
            }

            $pointsCost = (int) $item->points_cost * $quantity;
            $pointService = PointService::getInstance();
            if ($pointService->balance($customerId) < $pointsCost) {
                throw new RuntimeException(__('PointsMall::common.insufficient_points'));
            }

            // 扣分
            $pointService->change($customerId, -$pointsCost, 'mall_redeem', 0, __('PointsMall::common.log_redeem', ['title' => $item->title]));

            // 扣库存
            if ($item->stock >= 0) {
                $item->stock -= $quantity;
            }
            $item->redeemed_count += $quantity;
            $item->save();

            return Redemption::query()->create([
                'number'      => $this->generateNumber(),
                'item_id'     => $item->id,
                'customer_id' => $customerId,
                'title'       => $item->title,
                'points_cost' => $pointsCost,
                'cash_cost'   => (float) $item->cash_cost * $quantity,
                'quantity'    => $quantity,
                'status'      => 'pending',
                'contact'     => $contact,
            ]);
        });
    }

    protected function generateNumber(): string
    {
        do {
            $number = 'PM'.date('Ymd').strtoupper(Str::random(6));
        } while (Redemption::query()->where('number', $number)->exists());

        return $number;
    }
}
