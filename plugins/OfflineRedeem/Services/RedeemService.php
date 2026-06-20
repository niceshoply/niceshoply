<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OfflineRedeem\Services;

use Exception;
use Illuminate\Support\Str;
use Plugin\OfflineRedeem\Models\RedeemCode;

class RedeemService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('offline_redeem', 'enabled', true);
    }

    public function generate(string $title, string $type = 'voucher', int $refId = 0, int $customerId = 0, ?string $expiresAt = null): RedeemCode
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (RedeemCode::query()->where('code', $code)->exists());

        return RedeemCode::query()->create([
            'code'        => $code,
            'type'        => $type,
            'ref_id'      => $refId,
            'customer_id' => $customerId,
            'title'       => $title,
            'status'      => 'active',
            'expires_at'  => $expiresAt,
        ]);
    }

    /**
     * @throws Exception
     */
    public function verify(string $code): RedeemCode
    {
        $row = RedeemCode::query()->where('code', strtoupper(trim($code)))->first();
        if (! $row) {
            throw new Exception(__('OfflineRedeem::common.invalid'));
        }
        if ($row->status === 'redeemed') {
            throw new Exception(__('OfflineRedeem::common.already_redeemed'));
        }
        if ($row->expires_at && now()->gt($row->expires_at)) {
            throw new Exception(__('OfflineRedeem::common.expired'));
        }

        return $row;
    }

    /**
     * @throws Exception
     */
    public function redeem(string $code, string $staff = ''): RedeemCode
    {
        if (! $this->enabled()) {
            throw new Exception(__('OfflineRedeem::common.disabled'));
        }

        $token = (string) plugin_setting('offline_redeem', 'staff_token', '');
        if ($token !== '' && $staff !== $token) {
            throw new Exception(__('OfflineRedeem::common.invalid_staff'));
        }

        $row = $this->verify($code);
        $row->update(['status' => 'redeemed', 'redeemed_at' => now(), 'redeemed_by' => $staff ?: 'staff']);

        return $row->fresh();
    }
}
