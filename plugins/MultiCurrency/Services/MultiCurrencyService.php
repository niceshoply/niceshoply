<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiCurrency\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use NiceShoply\Common\Models\Currency;
use NiceShoply\Common\Repositories\CurrencyRepo;
use Plugin\MultiCurrency\Models\CurrencyRegionDefault;

class MultiCurrencyService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function list(): array
    {
        return currencies()->map(fn ($c) => [
            'code'          => $c->code,
            'name'          => $c->name,
            'symbol_left'   => $c->symbol_left,
            'symbol_right'  => $c->symbol_right,
            'decimal_place' => (int) $c->decimal_place,
            'rate'          => (float) $c->value,
        ])->values()->all();
    }

    public function switch(string $code): void
    {
        $code = strtolower(trim($code));
        if (currencies()->where('code', $code)->isEmpty()) {
            throw new Exception(__('MultiCurrency::common.invalid_currency'));
        }
        Session::put('currency', $code);
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $from = strtolower($from);
        $to   = strtolower($to);
        $fromRate = (float) (currencies()->firstWhere('code', $from)?->value ?? 1);
        $toRate   = (float) (currencies()->firstWhere('code', $to)?->value ?? 1);
        if ($fromRate <= 0) {
            $fromRate = 1;
        }

        return round($amount / $fromRate * $toRate, currency_decimal_place($to));
    }

    /**
     * 从 HTTP API 刷新汇率到核心 currencies 表。
     */
    public function refreshRates(): int
    {
        $url  = (string) plugin_setting('multi_currency', 'rate_api_url', '');
        $base = strtolower((string) plugin_setting('multi_currency', 'base_currency', 'usd'));
        if ($url === '') {
            throw new Exception(__('MultiCurrency::common.no_api'));
        }

        $resp = Http::timeout(15)->get($url);
        $rates = $resp->json('rates') ?? [];
        if (empty($rates)) {
            throw new Exception(__('MultiCurrency::common.rate_failed'));
        }

        $count = 0;
        foreach (currencies() as $currency) {
            $code = strtoupper($currency->code);
            if (isset($rates[$code])) {
                Currency::query()->whereKey($currency->id)->update(['value' => (float) $rates[$code]]);
                $count++;
            }
        }
        Currency::query()->where('code', $base)->update(['value' => 1]);
        CurrencyRepo::clearCache();

        return $count;
    }

    public function defaultForCountry(string $countryCode): ?string
    {
        $row = CurrencyRegionDefault::query()->where('country_code', strtoupper($countryCode))->first();

        return $row?->currency_code;
    }
}
