<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SignIn\Services;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Plugin\Points\Services\PointService;
use Plugin\SignIn\Models\SignInLog;

class SignInService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 执行签到。返回本次签到信息。
     *
     * @return array{points:int, continuous_days:int, date:string}
     * @throws RuntimeException 当天已签到时抛出
     */
    public function signIn(int $customerId): array
    {
        if ($customerId <= 0) {
            throw new RuntimeException(__('SignIn::common.need_login'));
        }

        $today = Carbon::today()->toDateString();

        return DB::transaction(function () use ($customerId, $today) {
            $exists = SignInLog::query()
                ->where('customer_id', $customerId)
                ->where('sign_date', $today)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new RuntimeException(__('SignIn::common.already_signed'));
            }

            $continuous = $this->calcContinuousDays($customerId, $today);
            $points     = $this->calcPoints($continuous);

            SignInLog::query()->create([
                'customer_id'     => $customerId,
                'sign_date'       => $today,
                'points'          => $points,
                'continuous_days' => $continuous,
            ]);

            if ($points > 0) {
                $this->awardPoints($customerId, $points, $continuous);
            }

            return [
                'points'          => $points,
                'continuous_days' => $continuous,
                'date'            => $today,
            ];
        });
    }

    /**
     * 计算包含今天在内的连续签到天数。
     */
    protected function calcContinuousDays(int $customerId, string $today): int
    {
        $yesterday = Carbon::parse($today)->subDay()->toDateString();

        $lastLog = SignInLog::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('sign_date')
            ->first();

        if ($lastLog && $lastLog->sign_date->toDateString() === $yesterday) {
            return (int) $lastLog->continuous_days + 1;
        }

        return 1;
    }

    /**
     * 基础积分 + 连续阶梯奖励。
     */
    protected function calcPoints(int $continuousDays): int
    {
        $base  = (int) plugin_setting('sign_in', 'base_points', 0);
        $bonus = 0;

        foreach ($this->parseStreakBonus() as $days => $extra) {
            if ($continuousDays === $days) {
                $bonus += $extra;
            }
        }

        return max($base + $bonus, 0);
    }

    /**
     * 解析连续签到奖励配置「7:20,30:100」=> [7=>20, 30=>100]。
     *
     * @return array<int,int>
     */
    public function parseStreakBonus(): array
    {
        $raw = trim((string) plugin_setting('sign_in', 'streak_bonus', ''));
        if ($raw === '') {
            return [];
        }

        $result = [];
        foreach (explode(',', $raw) as $pair) {
            $pair = trim($pair);
            if ($pair === '' || ! str_contains($pair, ':')) {
                continue;
            }
            [$days, $extra] = explode(':', $pair, 2);
            $days  = (int) trim($days);
            $extra = (int) trim($extra);
            if ($days > 0 && $extra > 0) {
                $result[$days] = $extra;
            }
        }

        return $result;
    }

    /**
     * 通过积分插件入账（积分插件未安装时静默跳过）。
     */
    protected function awardPoints(int $customerId, int $points, int $continuousDays): void
    {
        if (! class_exists(PointService::class)) {
            return;
        }

        PointService::getInstance()->change(
            $customerId,
            $points,
            'sign_in',
            0,
            __('SignIn::common.log_remark', ['days' => $continuousDays])
        );
    }

    /**
     * 获取签到状态：今天是否已签、当前连续天数、本月签到日列表。
     *
     * @return array{signed_today:bool, continuous_days:int, month_dates:array<int,string>}
     */
    public function status(int $customerId): array
    {
        if ($customerId <= 0) {
            return ['signed_today' => false, 'continuous_days' => 0, 'month_dates' => []];
        }

        $today = Carbon::today();

        $todayLog = SignInLog::query()
            ->where('customer_id', $customerId)
            ->where('sign_date', $today->toDateString())
            ->first();

        $lastLog = SignInLog::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('sign_date')
            ->first();

        $continuous = 0;
        if ($lastLog) {
            $diff = $lastLog->sign_date->diffInDays($today);
            if ($diff <= 1) {
                $continuous = (int) $lastLog->continuous_days;
            }
        }

        $monthDates = SignInLog::query()
            ->where('customer_id', $customerId)
            ->whereYear('sign_date', $today->year)
            ->whereMonth('sign_date', $today->month)
            ->orderBy('sign_date')
            ->pluck('sign_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->values()
            ->all();

        return [
            'signed_today'    => (bool) $todayLog,
            'continuous_days' => $continuous,
            'month_dates'     => $monthDates,
        ];
    }
}
