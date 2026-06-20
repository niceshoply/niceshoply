<?php
namespace Plugin\TaxEngine\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\TaxEngine\Services\TaxEngineService;

class TaxController extends BaseController
{
    public function estimate(Request $request): mixed
    {
        $service = TaxEngineService::getInstance();
        $rule    = $service->findRule(
            (string) $request->input('country_code', ''),
            (string) $request->input('region_code', '')
        );
        $amount  = (float) $request->input('amount', 0);
        $tax     = $service->calculate($amount, $rule);

        return json_success('ok', [
            'rule'           => $rule,
            'tax'            => $tax,
            'tax_format'     => currency_format($tax),
            'include_in_price' => (bool) ($rule?->include_in_price),
        ]);
    }

    public function validateVat(Request $request): mixed
    {
        $result = TaxEngineService::getInstance()->validateVatNumber(
            (string) $request->input('vat_number', ''),
            (string) $request->input('country_code', '')
        );

        return json_success('ok', $result);
    }
}
