<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LuckyDraw\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugin\LuckyDraw\Models\DrawRecord;
use Plugin\LuckyDraw\Models\Prize;
use Throwable;

class LuckyDrawService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('lucky_draw', 'enabled', true);
    }

    protected function drawsPerDay(): int
    {
        return max(1, (int) plugin_setting('lucky_draw', 'draws_per_day', 1));
    }

    public function activePrizes(): array
    {
        return Prize::query()
            ->where('is_active', true)
            ->orderBy('sort')->orderBy('id')
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'type' => $p->type])
            ->all();
    }

    public function remainingDraws(int $customerId): int
    {
        if ($customerId <= 0) {
            return 0;
        }
        $used = DrawRecord::query()
            ->where('customer_id', $customerId)
            ->whereDate('created_at', today())
            ->count();

        return max(0, $this->drawsPerDay() - $used);
    }

    /**
     * 执行一次抽奖。
     *
     * @throws Exception
     */
    public function draw(int $customerId): array
    {
        if ($customerId <= 0) {
            throw new Exception(__('LuckyDraw::common.need_login'));
        }
        if (! $this->enabled()) {
            throw new Exception(__('LuckyDraw::common.disabled'));
        }
        if ($this->remainingDraws($customerId) <= 0) {
            throw new Exception(__('LuckyDraw::common.no_chance'));
        }

        return DB::transaction(function () use ($customerId) {
            $prizes = Prize::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('stock', '<', 0)->orWhere('stock', '>', 0);
                })
                ->lockForUpdate()
                ->get();

            $prize = $this->weightedPick($prizes);

            // 无可中奖品时记为谢谢参与
            $prizeId   = $prize->id ?? 0;
            $prizeName = $prize->name ?? __('LuckyDraw::common.thanks');
            $prizeType = $prize->type ?? 'thanks';
            $resultVal = '';

            if ($prize) {
                if ($prize->stock > 0) {
                    $prize->decrement('stock');
                }
                $resultVal = $this->fulfill($prize, $customerId);
            }

            DrawRecord::query()->create([
                'customer_id'  => $customerId,
                'prize_id'     => $prizeId,
                'prize_name'   => $prizeName,
                'prize_type'   => $prizeType,
                'result_value' => $resultVal,
                'created_at'   => now(),
            ]);

            return [
                'prize_id'   => $prizeId,
                'prize_name' => $prizeName,
                'prize_type' => $prizeType,
                'value'      => $resultVal,
                'remaining'  => $this->remainingDraws($customerId),
            ];
        });
    }

    /**
     * 按权重随机选择奖品。
     */
    protected function weightedPick($prizes): ?Prize
    {
        $total = 0;
        foreach ($prizes as $p) {
            $total += max(0, (int) $p->weight);
        }
        if ($total <= 0) {
            return null;
        }

        $rand = random_int(1, $total);
        $acc  = 0;
        foreach ($prizes as $p) {
            $acc += max(0, (int) $p->weight);
            if ($rand <= $acc) {
                return $p;
            }
        }

        return null;
    }

    /**
     * 发放奖品，返回结果值（积分数或券码）。
     */
    protected function fulfill(Prize $prize, int $customerId): string
    {
        try {
            if ($prize->type === 'points' && is_numeric($prize->value)) {
                $amount = (int) $prize->value;
                $svc = '\Plugin\Points\Services\PointService';
                if ($amount > 0 && class_exists($svc)) {
                    $svc::getInstance()->change($customerId, $amount, 'lucky_draw', 0, __('LuckyDraw::common.points_remark'));
                }

                return (string) $amount;
            }

            if ($prize->type === 'coupon' && is_numeric($prize->value)) {
                $couponId = (int) $prize->value;
                if (Schema::hasTable('coupons')) {
                    $coupon = DB::table('coupons')->where('id', $couponId)->first();
                    if ($coupon) {
                        if (Schema::hasTable('coupon_claims')) {
                            DB::table('coupon_claims')->insert([
                                'coupon_id'   => $coupon->id,
                                'customer_id' => $customerId,
                                'code'        => $coupon->code,
                                'claimed_at'  => now(),
                            ]);
                        }

                        return (string) $coupon->code;
                    }
                }
            }
        } catch (Throwable $e) {
            // 发放异常不阻断抽奖记录
        }

        return '';
    }
}
