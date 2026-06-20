<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台退款单 Controller 测试。
 */
class RefundControllerTest extends ConsoleTestCase
{
    private function makeOrder(float $total = 100): Order
    {
        return Order::query()->create([
            'number'                 => 'RC-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Test',
            'email'                  => 'rc@example.com',
            'calling_code'           => 1,
            'telephone'              => '1234567890',
            'total'                  => $total,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => StateMachineService::PAID,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => 'Test',
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
            'billing_customer_name'  => 'Test',
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
        ]);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['refunds_index']);
        $this->get($this->consoleUrl('refunds.index'))->assertStatus(200);
    }

    public function test_store_creates_pending_refund(): void
    {
        $this->loginAdmin(['refunds_store']);
        $order = $this->makeOrder(120);

        $this->post($this->consoleUrl('refunds.store'), [
            'order_id' => $order->id,
            'amount'   => 40,
            'method'   => Refund::METHOD_MANUAL,
            'reason'   => '测试退款',
        ])->assertRedirect();

        $this->assertDatabaseHas('nice_refunds', [
            'order_id' => $order->id,
            'amount'   => 40,
            'method'   => Refund::METHOD_MANUAL,
            'status'   => 'pending',
        ]);
    }

    public function test_show_page_accessible(): void
    {
        $this->loginAdmin(['refunds_show']);

        $order  = $this->makeOrder();
        $refund = Refund::query()->create([
            'number'         => 'RFTEST'.uniqid(),
            'order_id'       => $order->id,
            'customer_id'    => 0,
            'amount'         => 25,
            'currency_code'  => 'USD',
            'currency_value' => 1,
            'method'         => Refund::METHOD_MANUAL,
            'status'         => 'pending',
        ]);

        $this->get($this->consoleUrl('refunds.show', $refund->id))->assertStatus(200);
    }

    public function test_process_manual_refund_succeeds(): void
    {
        $this->loginAdmin(['refunds_process']);

        $order  = $this->makeOrder();
        $refund = Refund::query()->create([
            'number'         => 'RFPROC'.uniqid(),
            'order_id'       => $order->id,
            'customer_id'    => 0,
            'amount'         => 20,
            'currency_code'  => 'USD',
            'currency_value' => 1,
            'method'         => Refund::METHOD_MANUAL,
            'status'         => 'pending',
        ]);

        $this->post($this->consoleUrl('refunds.process', $refund->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('succeeded', $refund->fresh()->status);
    }
}
