<?php
namespace Plugin\TaxEngine\Services;

use Plugin\TaxEngine\Models\TaxRule;

class TaxEngineService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (int) plugin_setting('tax_engine', 'enabled', 0) === 1;
    }

    public function applyOnShipping(): bool
    {
        return (int) plugin_setting('tax_engine', 'apply_on_shipping', 0) === 1;
    }

    public function findRule(?string $country, ?string $region = null): ?TaxRule
    {
        $country = strtoupper(trim((string) $country));
        if ($country === '') {
            return null;
        }
        $region = strtoupper(trim((string) $region));

        $query = TaxRule::query()->where('active', true)->where('country_code', $country);
        if ($region !== '') {
            $rule = (clone $query)->where('region_code', $region)->first();
            if ($rule) {
                return $rule;
            }
        }

        return $query->where(function ($q) {
            $q->whereNull('region_code')->orWhere('region_code', '');
        })->first();
    }

    public function calculate(float $taxable, ?TaxRule $rule): float
    {
        if (! $rule || $taxable <= 0) {
            return 0;
        }
        $rate = (float) $rule->rate;
        if ($rate <= 0) {
            return 0;
        }

        return round($taxable * $rate / 100, currency_decimal_place());
    }

    public function validateVatNumber(string $number, string $country = ''): array
    {
        $number  = strtoupper(preg_replace('/\s+/', '', $number));
        $country = strtoupper(trim($country));
        $valid   = strlen($number) >= 8 && preg_match('/^[A-Z0-9]+$/', $number);
        if ($country !== '' && ! str_starts_with($number, $country)) {
            $valid = $valid && str_starts_with($number, $country);
        }

        return ['valid' => (bool) $valid, 'normalized' => $number];
    }
}
