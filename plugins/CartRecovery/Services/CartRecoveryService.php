<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRecovery\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Plugin\CartRecovery\Models\CartRecoveryLog;
use Plugin\NotifyCenter\Services\NotifyService;

class CartRecoveryService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 扫描弃购购物车并发送召回站内信。返回成功召回的会员数。
     */
    public function scanAndNotify(): int
    {
        if (! class_exists(NotifyService::class)) {
            return 0;
        }

        $idleHours    = max(1, (int) plugin_setting('cart_recovery', 'idle_hours', 24));
        $cooldownDays = max(1, (int) plugin_setting('cart_recovery', 'cooldown_days', 7));

        $idleBefore = Carbon::now()->subHours($idleHours);
        $cooldown   = Carbon::now()->subDays($cooldownDays);

        // 找出闲置弃购的会员（购物车存在且最近更新早于阈值）
        $candidates = DB::table('cart_items')
            ->select('customer_id', DB::raw('COUNT(*) as item_count'), DB::raw('MAX(updated_at) as last_active'))
            ->where('customer_id', '>', 0)
            ->groupBy('customer_id')
            ->havingRaw('MAX(updated_at) < ?', [$idleBefore])
            ->get();

        $count = 0;
        foreach ($candidates as $row) {
            $customerId = (int) $row->customer_id;

            // 冷却期内已召回过则跳过
            $recentlySent = CartRecoveryLog::query()
                ->where('customer_id', $customerId)
                ->where('sent_at', '>=', $cooldown)
                ->exists();
            if ($recentlySent) {
                continue;
            }

            $this->notifyCustomer($customerId, (int) $row->item_count);
            $count++;
        }

        return $count;
    }

    protected function notifyCustomer(int $customerId, int $itemCount): void
    {
        $content = __('CartRecovery::common.recover_content', ['count' => $itemCount]);

        $code = trim((string) plugin_setting('cart_recovery', 'recovery_coupon_code', ''));
        if ($code !== '') {
            $content .= "\n".__('CartRecovery::common.coupon_line', ['code' => $code]);
        }

        NotifyService::getInstance()->notify(
            $customerId,
            __('CartRecovery::common.recover_title'),
            $content,
            'marketing'
        );

        CartRecoveryLog::query()->create([
            'customer_id' => $customerId,
            'item_count'  => $itemCount,
            'channel'     => 'inapp',
            'sent_at'     => Carbon::now(),
        ]);
    }
}
