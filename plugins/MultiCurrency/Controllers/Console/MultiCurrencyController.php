<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiCurrency\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\MultiCurrency\Models\CurrencyRegionDefault;
use Plugin\MultiCurrency\Services\MultiCurrencyService;

class MultiCurrencyController extends BaseController
{
    public function index(): mixed
    {
        $currencies = MultiCurrencyService::getInstance()->list();
        $regions    = CurrencyRegionDefault::query()->orderBy('country_code')->get();

        return nice_view('MultiCurrency::console.index', compact('currencies', 'regions'));
    }

    public function refresh(): mixed
    {
        try {
            $count = MultiCurrencyService::getInstance()->refreshRates();

            return json_success(__('MultiCurrency::common.refreshed', ['count' => $count]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function storeRegion(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'country_code'  => 'required|string|max:8',
                'currency_code' => 'required|string|max:8',
            ]);
            CurrencyRegionDefault::query()->updateOrCreate(
                ['country_code' => strtoupper($data['country_code'])],
                ['currency_code' => strtolower($data['currency_code'])]
            );

            return json_success(__('MultiCurrency::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
