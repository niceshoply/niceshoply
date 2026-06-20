<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Currency;

use Exception;
use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Currency;
use NiceShoply\Common\Repositories\CurrencyRepo;

/**
 * 汇率自动更新服务。
 *
 * 从外部 API 拉取最新汇率并更新 currencies 表。
 * 默认使用 Frankfurter（免费、无需 API Key），可通过 .env CURRENCY_RATES_URL 自定义。
 */
final class CurrencyRateUpdateService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * 拉取并更新所有启用币种的汇率。
     *
     * @param  string|null  $baseCode  基准币种，默认取 system_setting('currency')
     * @return array{updated: int, skipped: int, errors: array}
     *
     * @throws Exception
     */
    public function update(?string $baseCode = null): array
    {
        $baseCode = strtoupper($baseCode ?: (string) system_setting('currency', 'USD'));
        $url      = env('CURRENCY_RATES_URL') ?: "https://api.frankfurter.app/latest?from={$baseCode}";

        $response = Http::timeout(30)->get($url);
        if (! $response->successful()) {
            throw new Exception('汇率 API 请求失败：HTTP '.$response->status());
        }

        $data  = $response->json();
        $rates = $data['rates'] ?? [];
        if (empty($rates)) {
            throw new Exception('汇率 API 返回空数据');
        }

        // 基准币种自身 rate = 1
        $rates[$baseCode] = 1.0;

        $updated = 0;
        $skipped = 0;
        $errors  = [];

        $currencies = Currency::query()->where('active', true)->get();
        foreach ($currencies as $currency) {
            $code = strtoupper($currency->code);
            if (! isset($rates[$code])) {
                $skipped++;
                $errors[] = "币种 {$code} 不在 API 返回中，已跳过";

                continue;
            }

            $currency->value = round((float) $rates[$code], 8);
            $currency->save();
            $updated++;
        }

        CurrencyRepo::clearCache();

        return fire_hook_filter('service.currency_rate.update.result', [
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }
}
