<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Currency;
use NiceShoply\Common\Models\TaxRate;
use NiceShoply\Common\Models\TaxRule;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\Currency\CurrencyRateUpdateService;
use NiceShoply\Common\Services\Fee\Tax;
use NiceShoply\Common\Services\StateMachineService;
use ReflectionClass;
use Tests\TestCase;

/**
 * 多币种快照 + 税务合规集成测试。
 */
class MultiCurrencyTaxTest extends TestCase
{
    use DatabaseTransactions;

    public function test_order_create_locks_currency_snapshot(): void
    {
        Currency::query()->updateOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro', 'symbol_left' => '€', 'symbol_right' => '', 'decimal_place' => 2, 'value' => 0.92, 'active' => true]
        );

        $order = OrderRepo::getInstance()->create([
            'number'              => 'MC-'.uniqid(),
            'customer_id'         => 0,
            'total'               => 100,
            'currency_code'       => 'EUR',
            'currency_value'      => 0.92,
            'status'              => StateMachineService::CREATED,
            'shipping_address_id' => 0,
            'billing_address_id'  => 0,
        ]);

        $this->assertEquals('EUR', $order->currency_code);
        $this->assertEqualsWithDelta(0.92, (float) $order->currency_value, 0.0001);
        $this->assertNotNull($order->currency_snapshot_at);
    }

    public function test_currency_rate_update_from_api(): void
    {
        Http::fake([
            '*' => Http::response([
                'base'  => 'USD',
                'rates' => ['USD' => 1, 'EUR' => 0.91, 'CNY' => 7.2],
            ], 200),
        ]);

        Currency::query()->updateOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_place' => 2, 'value' => 1, 'active' => true]
        );
        Currency::query()->updateOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro', 'symbol_left' => '€', 'symbol_right' => '', 'decimal_place' => 2, 'value' => 0.5, 'active' => true]
        );

        config(['nice.currency' => 'USD']);
        config(['nice.system.currency_auto_update' => true]);

        $result = CurrencyRateUpdateService::getInstance()->update('USD');

        $this->assertGreaterThanOrEqual(1, $result['updated']);
        $this->assertEquals(0.91, (float) Currency::query()->where('code', 'EUR')->value('value'));
    }

    public function test_tax_rule_cross_border_flag_persisted(): void
    {
        $rate = TaxRate::query()->create([
            'region_id' => 1,
            'name'      => 'Cross VAT',
            'type'      => 'percent',
            'rate'      => 10,
            'scheme'    => 'vat',
        ]);

        $rule = TaxRule::query()->create([
            'tax_class_id' => 1,
            'tax_rate_id'  => $rate->id,
            'based'        => 'shipping',
            'priority'     => 1,
            'cross_border' => true,
        ]);

        $this->assertTrue($rule->fresh()->cross_border);
        $this->assertEquals('vat', $rate->fresh()->scheme);
    }

    public function test_tax_discount_ratio_when_setting_enabled(): void
    {
        config(['nice.system.tax_base_include_discount' => true]);

        $service = \NiceShoply\Common\Services\CheckoutService::getInstance(0, 'tax-guest-'.uniqid());
        $ref     = new ReflectionClass($service);

        $cartProp = $ref->getProperty('cartList');
        $cartProp->setAccessible(true);
        $cartProp->setValue($service, [
            ['subtotal' => 200, 'quantity' => 1, 'price' => 200, 'is_virtual' => false, 'weight' => 1, 'tax_class_id' => 0],
        ]);

        $discountProp = $ref->getProperty('appliedDiscounts');
        $discountProp->setAccessible(true);
        $discountProp->setValue($service, [['amount' => 50]]);

        $feeProp = $ref->getProperty('feeList');
        $feeProp->setAccessible(true);
        $feeProp->setValue($service, [['code' => 'subtotal', 'total' => 200]]);

        $taxFee      = new Tax($service);
        $ratioMethod = (new ReflectionClass(Tax::class))->getMethod('getDiscountRatio');
        $ratioMethod->setAccessible(true);
        $ratio = $ratioMethod->invoke($taxFee);

        // 200 - 50 = 150，比例 0.75
        $this->assertEqualsWithDelta(0.75, $ratio, 0.001);
    }
}
