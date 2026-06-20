<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GiftCard\Services;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Customer\Transaction;
use Plugin\GiftCard\Models\GiftCard;
use Plugin\GiftCard\Models\GiftCardBatch;

class GiftCardService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 生成一批礼品卡。
     */
    public function generateBatch(string $name, float $faceValue, int $quantity, string $prefix = '', ?string $expireAt = null): GiftCardBatch
    {
        $quantity = max(1, min($quantity, 10000));

        return DB::transaction(function () use ($name, $faceValue, $quantity, $prefix, $expireAt) {
            $batch = GiftCardBatch::query()->create([
                'name'       => $name,
                'face_value' => $faceValue,
                'quantity'   => $quantity,
                'prefix'     => $prefix ?: null,
                'expire_at'  => $expireAt ?: null,
            ]);

            $rows = [];
            for ($i = 0; $i < $quantity; $i++) {
                $rows[] = [
                    'batch_id'    => $batch->id,
                    'code'        => $this->generateCode($prefix),
                    'pin'         => strtoupper(Str::random(12)),
                    'face_value'  => $faceValue,
                    'balance'     => $faceValue,
                    'status'      => 'unused',
                    'customer_id' => 0,
                    'expire_at'   => $expireAt ?: null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                GiftCard::query()->insert($chunk);
            }

            return $batch;
        });
    }

    protected function generateCode(string $prefix = ''): string
    {
        do {
            $code = strtoupper($prefix).strtoupper(Str::random(max(4, 16 - strlen($prefix))));
        } while (GiftCard::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * 兑换礼品卡：校验卡密/状态/有效期，余额充值到会员账户。
     *
     * @throws RuntimeException
     */
    public function redeem(int $customerId, string $code, string $pin): float
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('GiftCard::common.need_login'));
        }

        return DB::transaction(function () use ($customerId, $code, $pin) {
            /** @var GiftCard|null $card */
            $card = GiftCard::query()
                ->where('code', strtoupper(trim($code)))
                ->lockForUpdate()
                ->first();

            if (! $card || ! hash_equals($card->pin, strtoupper(trim($pin)))) {
                throw new RuntimeException(__('GiftCard::common.invalid_card'));
            }
            if ($card->status === 'disabled') {
                throw new RuntimeException(__('GiftCard::common.card_disabled'));
            }
            if ($card->status === 'used' || $card->balance <= 0) {
                throw new RuntimeException(__('GiftCard::common.card_used'));
            }
            if ($card->expire_at && Carbon::parse($card->expire_at)->endOfDay()->isPast()) {
                throw new RuntimeException(__('GiftCard::common.card_expired'));
            }

            $amount = (float) $card->balance;

            $this->creditBalance($customerId, $amount, __('GiftCard::common.tx_comment', ['code' => $card->code]));

            $card->balance     = 0;
            $card->status      = 'used';
            $card->customer_id = $customerId;
            $card->redeemed_at = now();
            $card->save();

            return $amount;
        });
    }

    /**
     * 充值到会员余额：写入 customer_transactions 并更新 customers.balance。
     */
    protected function creditBalance(int $customerId, float $amount, string $comment): void
    {
        $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

        $newBalance = round((float) $customer->balance + $amount, 2);

        Transaction::query()->create([
            'customer_id' => $customerId,
            'amount'      => $amount,
            'type'        => Transaction::TYPE_RECHARGE,
            'comment'     => $comment,
            'balance'     => $newBalance,
        ]);

        $customer->balance = $newBalance;
        $customer->save();
    }
}
