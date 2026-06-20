<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Member;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\PointLog;
use NiceShoply\Common\Repositories\CustomerPointRepo;
use NiceShoply\Common\Repositories\PointLogRepo;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Member\MemberLevelService as MemberLevelServiceAlias;
use Throwable;

/**
 * 积分服务：获取/消费/过期/调整 + 订单联动。
 */
class PointService extends BaseService
{
    public const SOURCE_ORDER = 'order';

    public const SOURCE_REFUND = 'refund';

    public const SOURCE_ADMIN = 'admin';

    /**
     * 积分功能是否启用。
     */
    public function isEnabled(): bool
    {
        return (bool) system_setting('points_enabled', false);
    }

    /**
     * 每消费 1 元获得积分数。
     */
    public function getEarnRate(): float
    {
        return max(0, (float) system_setting('points_earn_rate', 1));
    }

    /**
     * 多少积分抵扣 1 元。
     */
    public function getRedeemRate(): float
    {
        return max(1, (float) system_setting('points_redeem_rate', 100));
    }

    /**
     * 单笔订单最多抵扣订单小计（扣促销后）的百分比。
     */
    public function getMaxRedeemPercent(): float
    {
        return min(100, max(0, (float) system_setting('points_max_redeem_percent', 50)));
    }

    /**
     * 积分有效期（天），0 表示不过期。
     */
    public function getExpireDays(): int
    {
        return max(0, (int) system_setting('points_expire_days', 0));
    }

    /**
     * 读取客户可用积分。
     */
    public function getBalance(int $customerId): int
    {
        if ($customerId <= 0) {
            return 0;
        }

        $account = CustomerPointRepo::getInstance()->findByCustomerId($customerId);

        return (int) ($account->balance ?? 0);
    }

    /**
     * 校验并计算结账可用积分抵现。
     *
     * @return array{valid: bool, message: string, points: int, amount: float}
     */
    public function validateRedeem(CheckoutService $checkout, int $pointsToUse): array
    {
        if (! $this->isEnabled()) {
            return $this->failRedeem(trans('front/point.disabled'));
        }

        $customerId = $checkout->getCustomerId();
        if ($customerId <= 0) {
            return $this->failRedeem(trans('front/point.login_required'));
        }

        $pointsToUse = max(0, $pointsToUse);
        if ($pointsToUse <= 0) {
            return $this->failRedeem(trans('front/point.invalid_points'));
        }

        $balance = $this->getBalance($customerId);
        if ($pointsToUse > $balance) {
            return $this->failRedeem(trans('front/point.insufficient'));
        }

        $amount    = $this->pointsToAmount($pointsToUse);
        $maxAmount = $this->maxRedeemAmount($checkout);
        if ($maxAmount <= 0) {
            return $this->failRedeem(trans('front/point.cannot_redeem'));
        }

        if ($amount > $maxAmount) {
            $pointsToUse = $this->amountToPoints($maxAmount);
            $amount      = $this->pointsToAmount($pointsToUse);
        }

        return [
            'valid'   => true,
            'message' => '',
            'points'  => $pointsToUse,
            'amount'  => round($amount, 2),
        ];
    }

    /**
     * 订单进入 unpaid 时扣减积分（幂等）。
     *
     * @throws Throwable
     */
    public function redeemForOrder(Order $order): void
    {
        if (! $this->isEnabled() || ! $order->customer_id) {
            return;
        }

        $fee = $order->fees()->where('code', 'points')->first();
        if (! $fee) {
            return;
        }

        $reference = is_array($fee->reference) ? $fee->reference : [];
        $points    = (int) ($reference['points'] ?? 0);
        if ($points <= 0) {
            return;
        }

        if (PointLogRepo::getInstance()->existsForReference(
            (int) $order->customer_id,
            PointLog::TYPE_SPEND,
            self::SOURCE_ORDER,
            (int) $order->id
        )) {
            return;
        }

        $this->spend(
            (int) $order->customer_id,
            $points,
            self::SOURCE_ORDER,
            (int) $order->id,
            trans('front/point.spend_order', ['number' => $order->number])
        );
    }

    /**
     * 订单支付成功后发放积分（幂等）。
     *
     * @throws Throwable
     */
    public function earnForPaidOrder(Order $order): void
    {
        if (! $this->isEnabled() || ! $order->customer_id) {
            return;
        }

        if (PointLogRepo::getInstance()->existsForReference(
            (int) $order->customer_id,
            PointLog::TYPE_EARN,
            self::SOURCE_ORDER,
            (int) $order->id
        )) {
            return;
        }

        $points = (int) floor((float) $order->total * $this->getEarnRate());
        if ($points <= 0) {
            return;
        }

        $expiresAt  = null;
        $expireDays = $this->getExpireDays();
        if ($expireDays > 0) {
            $expiresAt = Carbon::now()->addDays($expireDays);
        }

        $this->earn(
            (int) $order->customer_id,
            $points,
            self::SOURCE_ORDER,
            (int) $order->id,
            trans('front/point.earn_order', ['number' => $order->number]),
            $expiresAt
        );

        MemberLevelServiceAlias::getInstance()->dispatchRecalculate((int) $order->customer_id);
    }

    /**
     * 订单取消/退款时回滚积分。
     *
     * @throws Throwable
     */
    public function rollbackOrder(Order $order, bool $reverseEarned): void
    {
        if (! $order->customer_id) {
            return;
        }

        $this->rollbackSpentForOrder($order);

        if ($reverseEarned) {
            $this->rollbackEarnedForOrder($order);
        }

        MemberLevelServiceAlias::getInstance()->dispatchRecalculate((int) $order->customer_id);
    }

    /**
     * 退款成功 Hook：满额退款时回滚获得积分并返还抵扣积分。
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws Throwable
     */
    public function handleRefundSucceeded(array $payload): void
    {
        if (empty($payload['full_refund']) || empty($payload['refund'])) {
            return;
        }

        $refund = $payload['refund'];
        $order  = $refund->order ?? null;
        if (! $order instanceof Order) {
            return;
        }

        $this->rollbackOrder($order, true);
    }

    /**
     * 扫描并过期积分。
     *
     * @throws Throwable
     */
    public function expireDuePoints(?int $customerId = null): int
    {
        $query = PointLog::query()
            ->where('type', PointLog::TYPE_EARN)
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now());

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $expiredCount = 0;
        foreach ($query->get() as $log) {
            if (PointLogRepo::getInstance()->existsForReference(
                (int) $log->customer_id,
                PointLog::TYPE_EXPIRE,
                self::SOURCE_ORDER,
                (int) $log->id
            )) {
                continue;
            }

            $this->expire((int) $log->customer_id, (int) $log->points, (int) $log->id);
            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * 后台手动调整积分。
     *
     * @throws Throwable
     */
    public function adjust(int $customerId, int $points, string $comment = ''): void
    {
        if ($customerId <= 0 || $points === 0) {
            return;
        }

        DB::transaction(function () use ($customerId, $points, $comment) {
            $account = CustomerPointRepo::getInstance()->getOrCreateForUpdate($customerId);

            if ($points > 0) {
                $account->balance += $points;
                $account->total_earned += $points;
            } else {
                $deduct = min($account->balance, abs($points));
                $account->balance -= $deduct;
                $account->total_spent += $deduct;
                $points = -$deduct;
            }

            $account->save();

            PointLog::query()->create([
                'customer_id'  => $customerId,
                'type'         => PointLog::TYPE_ADJUST,
                'points'       => $points,
                'source'       => self::SOURCE_ADMIN,
                'reference_id' => 0,
                'comment'      => $comment,
            ]);
        });

        MemberLevelServiceAlias::getInstance()->dispatchRecalculate($customerId);
    }

    /**
     * @throws Throwable
     */
    private function earn(
        int $customerId,
        int $points,
        string $source,
        int $referenceId,
        string $comment = '',
        ?Carbon $expiresAt = null
    ): void {
        DB::transaction(function () use ($customerId, $points, $source, $referenceId, $comment, $expiresAt) {
            $account = CustomerPointRepo::getInstance()->getOrCreateForUpdate($customerId);
            $account->balance += $points;
            $account->total_earned += $points;
            $account->save();

            PointLog::query()->create([
                'customer_id'  => $customerId,
                'type'         => PointLog::TYPE_EARN,
                'points'       => $points,
                'source'       => $source,
                'reference_id' => $referenceId,
                'expires_at'   => $expiresAt,
                'comment'      => $comment,
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    private function spend(int $customerId, int $points, string $source, int $referenceId, string $comment = ''): void
    {
        DB::transaction(function () use ($customerId, $points, $source, $referenceId, $comment) {
            $account = CustomerPointRepo::getInstance()->getOrCreateForUpdate($customerId);
            if ($account->balance < $points) {
                throw new Exception(trans('front/point.insufficient'));
            }

            $account->balance -= $points;
            $account->total_spent += $points;
            $account->save();

            PointLog::query()->create([
                'customer_id'  => $customerId,
                'type'         => PointLog::TYPE_SPEND,
                'points'       => -$points,
                'source'       => $source,
                'reference_id' => $referenceId,
                'comment'      => $comment,
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    private function expire(int $customerId, int $points, int $earnLogId): void
    {
        DB::transaction(function () use ($customerId, $points, $earnLogId) {
            $account = CustomerPointRepo::getInstance()->getOrCreateForUpdate($customerId);
            $deduct  = min($account->balance, $points);
            if ($deduct <= 0) {
                return;
            }

            $account->balance -= $deduct;
            $account->save();

            PointLog::query()->create([
                'customer_id'  => $customerId,
                'type'         => PointLog::TYPE_EXPIRE,
                'points'       => -$deduct,
                'source'       => self::SOURCE_ORDER,
                'reference_id' => $earnLogId,
                'comment'      => trans('console/point.expired'),
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    private function rollbackSpentForOrder(Order $order): void
    {
        $spendLog = PointLog::query()
            ->where('customer_id', $order->customer_id)
            ->where('type', PointLog::TYPE_SPEND)
            ->where('source', self::SOURCE_ORDER)
            ->where('reference_id', $order->id)
            ->first();

        if (! $spendLog) {
            return;
        }

        $points = abs((int) $spendLog->points);
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $spendLog, $points) {
            $account = CustomerPointRepo::getInstance()->getOrCreateForUpdate((int) $order->customer_id);
            $account->balance += $points;
            $account->total_spent = max(0, $account->total_spent - $points);
            $account->save();

            $spendLog->delete();
        });
    }

    /**
     * @throws Throwable
     */
    private function rollbackEarnedForOrder(Order $order): void
    {
        $earnLog = PointLog::query()
            ->where('customer_id', $order->customer_id)
            ->where('type', PointLog::TYPE_EARN)
            ->where('source', self::SOURCE_ORDER)
            ->where('reference_id', $order->id)
            ->first();

        if (! $earnLog) {
            return;
        }

        $points = (int) $earnLog->points;
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $earnLog, $points) {
            $account               = CustomerPointRepo::getInstance()->getOrCreateForUpdate((int) $order->customer_id);
            $deduct                = min($account->balance, $points);
            $account->balance      = max(0, $account->balance - $deduct);
            $account->total_earned = max(0, $account->total_earned - $points);
            $account->save();

            $earnLog->delete();
        });
    }

    private function maxRedeemAmount(CheckoutService $checkout): float
    {
        $remaining = $checkout->getDiscountedSubtotalRemaining();
        $percent   = $this->getMaxRedeemPercent();

        return round($remaining * $percent / 100, currency_decimal_place());
    }

    private function pointsToAmount(int $points): float
    {
        return round($points / $this->getRedeemRate(), currency_decimal_place());
    }

    private function amountToPoints(float $amount): int
    {
        return (int) floor($amount * $this->getRedeemRate());
    }

    /**
     * @return array{valid: bool, message: string, points: int, amount: float}
     */
    private function failRedeem(string $message): array
    {
        return [
            'valid'   => false,
            'message' => $message,
            'points'  => 0,
            'amount'  => 0.0,
        ];
    }
}
