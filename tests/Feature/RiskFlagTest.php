<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\Compliance\LoginSecurityService;
use NiceShoply\Common\Services\Compliance\OrderRiskService;
use Tests\TestCase;

/**
 * 订单风险标记与下单频控集成测试。
 */
class RiskFlagTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nice.system.risk_order_max_amount'           => 1000,
            'nice.system.risk_high_score_threshold'       => 30,
            'nice.system.risk_order_frequency_limit'      => 3,
            'nice.system.risk_order_frequency_hours'      => 1,
            'nice.system.order_rate_limit_enabled'        => true,
            'nice.system.order_rate_limit_customer'       => 2,
            'nice.system.order_rate_limit_window_minutes' => 60,
        ]);
    }

    private function makeOrder(Customer $customer, float $total, array $extra = []): Order
    {
        return Order::query()->create(array_merge([
            'number'                 => 'RISK-'.uniqid(),
            'customer_id'            => $customer->id,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => $customer->name,
            'email'                  => $customer->email,
            'calling_code'           => 86,
            'telephone'              => '13800000000',
            'total'                  => $total,
            'locale'                 => 'zh-cn',
            'currency_code'          => 'CNY',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => 'unpaid',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat',
            'shipping_country'       => 'CN',
            'billing_country'        => 'US',
            'shipping_customer_name' => 'T',
            'shipping_calling_code'  => '86',
            'shipping_telephone'     => '13800000000',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'GD',
            'shipping_city'          => 'SZ',
            'shipping_address_1'     => 'Test',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '518000',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'COD',
            'billing_customer_name'  => 'T',
            'billing_calling_code'   => '86',
            'billing_telephone'      => '13800000000',
            'billing_country_id'     => 2,
            'billing_state_id'       => 1,
            'billing_state'          => 'CA',
            'billing_city'           => 'LA',
            'billing_address_1'      => 'Test',
            'billing_address_2'      => '',
            'billing_zipcode'        => '90001',
        ], $extra));
    }

    public function test_high_amount_and_address_mismatch_flags_order(): void
    {
        $customer = Customer::query()->create([
            'email'             => 'risk-'.uniqid().'@example.com',
            'password'          => bcrypt('x'),
            'name'              => 'Risk',
            'customer_group_id' => 0,
            'active'            => true,
        ]);

        $order = $this->makeOrder($customer, 5000);
        $order->load('customer');

        $result = OrderRiskService::getInstance()->evaluateAndPersist($order);

        $this->assertContains(OrderRiskService::FLAG_HIGH_AMOUNT, $result['flags']);
        $this->assertContains(OrderRiskService::FLAG_ADDRESS_MISMATCH, $result['flags']);
        $this->assertTrue($result['high_risk']);

        $order->refresh();
        $this->assertTrue($order->is_high_risk);
        $this->assertGreaterThanOrEqual(30, $order->risk_score);
    }

    public function test_order_rate_limit_blocks_excessive_orders(): void
    {
        $customer = Customer::query()->create([
            'email'             => 'rate-'.uniqid().'@example.com',
            'password'          => bcrypt('x'),
            'name'              => 'Rate',
            'customer_group_id' => 0,
            'active'            => true,
        ]);

        $this->makeOrder($customer, 10);
        $this->makeOrder($customer, 10);

        $this->expectException(\Exception::class);
        LoginSecurityService::getInstance()->assertOrderRateAllowed($customer->id, '127.0.0.1');
    }
}
