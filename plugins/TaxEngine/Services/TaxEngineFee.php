<?php
namespace Plugin\TaxEngine\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

class TaxEngineFee extends BaseService
{
    /**
     * @throws Throwable
     */
    public function addFee(): void
    {
        $service = TaxEngineService::getInstance();
        if (! $service->enabled()) {
            return;
        }

        $address = $this->checkoutService->getShippingAddress();
        $rule    = $service->findRule($address['country_code'] ?? '', $address['zone_code'] ?? $address['state'] ?? '');
        if (! $rule) {
            return;
        }

        $taxable = $this->checkoutService->getSubTotal();
        if ($service->applyOnShipping()) {
            $taxable += (new \NiceShoply\Common\Services\Fee\Shipping($this->checkoutService))->getShippingFee();
        }

        $amount = $service->calculate($taxable, $rule);
        if ($amount <= 0) {
            return;
        }

        $title = $rule->name.' ('.strtoupper($rule->tax_type).' '.$rule->rate.'%)';
        $this->checkoutService->addFeeList([
            'code'         => 'tax_engine',
            'title'        => $title,
            'total'        => $amount,
            'total_format' => currency_format($amount),
        ]);
    }
}
