<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Payment;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Services\Finance\ReconciliationService;
use NiceShoply\Common\Services\StateMachineService;
use Tests\TestCase;

/**
 * 财务对账服务测试。
 */
class ReconciliationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOrder(float $total, string $status = StateMachineService::PAID): Order
    {
        return Order::query()->create([
            'number'                 => 'REC-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Rec',
            'email'                  => 'rec@example.com',
            'calling_code'           => 1,
            'telephone'              => '1234567890',
            'total'                  => $total,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => $status,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => 'Rec',
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '1234567890',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'CA',
            'shipping_city'          => 'LA',
            'shipping_address_1'     => '123 St',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '90001',
            'billing_method_code'    => 'bank_transfer',
            'billing_method_name'    => 'Bank',
            'billing_customer_name'  => 'Rec',
            'billing_calling_code'   => '1',
            'billing_telephone'      => '1234567890',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'CA',
            'billing_city'           => 'LA',
            'billing_address_1'      => '123 St',
            'billing_address_2'      => '',
            'billing_zipcode'        => '90001',
            'created_at'             => Carbon::now(),
        ]);
    }

    public function test_summarize_matches_orders_and_refunds(): void
    {
        $today = Carbon::today()->toDateString();
        $order = $this->makeOrder(200);

        Payment::query()->create([
            'order_id'     => $order->id,
            'charge_id'    => 'ch_'.uniqid(),
            'amount'       => 200,
            'handling_fee' => 5,
            'paid'         => true,
        ]);

        Refund::query()->create([
            'number'         => 'RFREC'.uniqid(),
            'order_id'       => $order->id,
            'customer_id'    => 0,
            'amount'         => 30,
            'currency_code'  => 'USD',
            'currency_value' => 1,
            'method'         => Refund::METHOD_MANUAL,
            'status'         => 'succeeded',
            'processed_at'   => Carbon::now(),
        ]);

        $summary = ReconciliationService::getInstance()->summarize($today, $today);

        $this->assertEquals(200, $summary['income']);
        $this->assertEquals(30, $summary['refunds']);
        $this->assertEquals(5, $summary['fees']);
        $this->assertEquals(165, $summary['net']);
        $this->assertEquals(1, $summary['order_count']);
        $this->assertEquals(1, $summary['refund_count']);
    }
}
