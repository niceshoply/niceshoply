<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Customer\Transaction;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Refund;
use NiceShoply\Common\Services\Promotion\CouponService;
use NiceShoply\Common\Services\Refund\RefundService;
use NiceShoply\Common\Services\StateMachineService;
use Tests\TestCase;

/**
 * 退款单闭环集成测试。
 *
 * 覆盖：创建/处理/取消状态机、余额退款入账、满额退款券回滚、超额拦截。
 */
class RefundFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'refund-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'Refund Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();
    }

    private function makeOrder(int $customerId = 0, float $total = 100): Order
    {
        return Order::query()->create([
            'number'                 => 'RF-'.uniqid(),
            'customer_id'            => $customerId,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Refund Customer',
            'email'                  => 'refund-order@example.com',
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
            'shipping_customer_name' => 'Refund Customer',
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '1234567890',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'CA',
            'shipping_city'          => 'Los Angeles',
            'shipping_address_1'     => '123 Test St',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '90001',
            'billing_method_code'    => 'bank_transfer',
            'billing_method_name'    => 'Bank Transfer',
            'billing_customer_name'  => 'Refund Customer',
            'billing_calling_code'   => '1',
            'billing_telephone'      => '1234567890',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'CA',
            'billing_city'           => 'Los Angeles',
            'billing_address_1'      => '123 Test St',
            'billing_address_2'      => '',
            'billing_zipcode'        => '90001',
        ])->refresh();
    }

    public function test_balance_refund_credits_customer_wallet(): void
    {
        $customer = $this->makeCustomer();
        $order    = $this->makeOrder($customer->id, 80);

        $refund = RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 50,
            'method'   => Refund::METHOD_BALANCE,
        ]);

        $this->assertEquals('pending', $refund->status);

        $refund = RefundService::getInstance()->process($refund);

        $this->assertEquals('succeeded', $refund->status);
        $this->assertDatabaseHas('customer_transactions', [
            'customer_id' => $customer->id,
            'type'        => Transaction::TYPE_REFUND,
            'amount'      => 50,
        ]);
        $this->assertEquals(50, (float) $customer->fresh()->balance);
    }

    public function test_manual_refund_succeeds_without_gateway(): void
    {
        $order  = $this->makeOrder(0, 60);
        $refund = RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 30,
            'method'   => Refund::METHOD_MANUAL,
        ]);

        $refund = RefundService::getInstance()->process($refund);

        $this->assertEquals('succeeded', $refund->status);
        $this->assertNotNull($refund->processed_at);
    }

    public function test_cancel_pending_refund(): void
    {
        $order  = $this->makeOrder(0, 100);
        $refund = RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 20,
            'method'   => Refund::METHOD_MANUAL,
        ]);

        $refund = RefundService::getInstance()->cancel($refund, '测试取消');

        $this->assertEquals('cancelled', $refund->status);
        $this->assertDatabaseHas('nice_refund_logs', [
            'refund_id' => $refund->id,
            'to_status' => 'cancelled',
        ]);
    }

    public function test_amount_exceeds_order_is_rejected(): void
    {
        $order = $this->makeOrder(0, 100);

        $this->expectException(Exception::class);
        RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 150,
            'method'   => Refund::METHOD_MANUAL,
        ]);
    }

    public function test_full_refund_rolls_back_coupon_usage(): void
    {
        $customer = $this->makeCustomer();
        $order    = $this->makeOrder($customer->id, 100);

        $coupon = Coupon::query()->create([
            'code'       => 'RF'.strtoupper(uniqid()),
            'type'       => 'fixed',
            'value'      => 10,
            'used_count' => 0,
            'active'     => true,
        ]);

        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 10);
        $this->assertEquals(1, $coupon->fresh()->used_count);

        $refund = RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 100,
            'method'   => Refund::METHOD_MANUAL,
        ]);
        RefundService::getInstance()->process($refund);

        $this->assertEquals(0, $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('nice_coupon_usages', [
            'coupon_id' => $coupon->id,
            'order_id'  => $order->id,
        ]);
    }

    public function test_partial_refund_does_not_roll_back_coupon(): void
    {
        $customer = $this->makeCustomer();
        $order    = $this->makeOrder($customer->id, 100);

        $coupon = Coupon::query()->create([
            'code'       => 'PR'.strtoupper(uniqid()),
            'type'       => 'fixed',
            'value'      => 10,
            'used_count' => 0,
            'active'     => true,
        ]);

        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 10);

        $refund = RefundService::getInstance()->create([
            'order_id' => $order->id,
            'amount'   => 50,
            'method'   => Refund::METHOD_MANUAL,
        ]);
        RefundService::getInstance()->process($refund);

        $this->assertEquals(1, $coupon->fresh()->used_count);
        $this->assertDatabaseHas('nice_coupon_usages', [
            'coupon_id' => $coupon->id,
            'order_id'  => $order->id,
        ]);
    }
}
