<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiCurrency\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\MultiCurrency\Services\MultiCurrencyService;
use Throwable;

class CurrencyController extends BaseController
{
    public function list(): mixed
    {
        return json_success('ok', [
            'current'    => current_currency_code(),
            'currencies' => MultiCurrencyService::getInstance()->list(),
        ]);
    }

    public function switch(Request $request): mixed
    {
        try {
            MultiCurrencyService::getInstance()->switch((string) $request->input('code', ''));

            return json_success('ok', ['code' => current_currency_code()]);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    public function convert(Request $request): mixed
    {
        $amount = (float) $request->input('amount', 0);
        $from   = (string) $request->input('from', setting_currency_code());
        $to     = (string) $request->input('to', current_currency_code());

        return json_success('ok', [
            'amount'        => $amount,
            'converted'     => MultiCurrencyService::getInstance()->convert($amount, $from, $to),
            'converted_format' => currency_format(MultiCurrencyService::getInstance()->convert($amount, $from, $to), $to),
        ]);
    }
}
