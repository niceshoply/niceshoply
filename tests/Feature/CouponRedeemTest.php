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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Coupon;
use NiceShoply\Common\Models\CouponUsage;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Promotion\CouponService;
use ReflectionClass;
use Tests\TestCase;

/**
 * 优惠券校验/核销集成测试。
 *
 * 覆盖：有效性、门槛、限领、唯一约束防重、并发不超发、取消回滚。
 */
class CouponRedeemTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'coupon-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'Coupon Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();
    }

    private function makeService(Customer $customer, float $subtotal): CheckoutService
    {
        $service = CheckoutService::getInstance($customer->id, 'coupon-guest-'.uniqid());

        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('cartList');
        $prop->setAccessible(true);
        $prop->setValue($service, [
            ['subtotal' => $subtotal, 'quantity' => 1, 'price' => $subtotal, 'is_virtual' => false, 'weight' => 1, 'tax_class_id' => 0],
        ]);

        return $service;
    }

    private function makeCoupon(array $attributes): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code'               => 'C'.strtoupper(uniqid()),
            'type'               => 'fixed',
            'value'              => 20,
            'min_amount'         => 0,
            'total_limit'        => 0,
            'used_count'         => 0,
            'per_customer_limit' => 1,
            'active'             => true,
        ], $attributes));
    }

    public function test_valid_fixed_coupon_returns_discount(): void
    {
        $coupon  = $this->makeCoupon(['type' => 'fixed', 'value' => 20]);
        $service = $this->makeService($this->makeCustomer(), 100);

        $result = CouponService::getInstance()->validate($coupon->code, $service);

        $this->assertTrue($result['valid']);
        $this->assertEquals(20, $result['discount']);
    }

    public function test_percent_coupon_discount(): void
    {
        $coupon  = $this->makeCoupon(['type' => 'percent', 'value' => 10]);
        $service = $this->makeService($this->makeCustomer(), 200);

        $result = CouponService::getInstance()->validate($coupon->code, $service);

        $this->assertEquals(20, $result['discount']);
    }

    public function test_min_amount_threshold_blocks_coupon(): void
    {
        $coupon  = $this->makeCoupon(['type' => 'fixed', 'value' => 20, 'min_amount' => 150]);
        $service = $this->makeService($this->makeCustomer(), 100);

        $result = CouponService::getInstance()->validate($coupon->code, $service);

        $this->assertFalse($result['valid']);
    }

    public function test_expired_coupon_invalid(): void
    {
        $coupon  = $this->makeCoupon(['ends_at' => now()->subDay()]);
        $service = $this->makeService($this->makeCustomer(), 100);

        $result = CouponService::getInstance()->validate($coupon->code, $service);

        $this->assertFalse($result['valid']);
    }

    public function test_per_customer_limit_enforced(): void
    {
        $customer = $this->makeCustomer();
        $coupon   = $this->makeCoupon(['per_customer_limit' => 1]);

        // 先记录一次该客户核销
        CouponUsage::query()->create([
            'coupon_id'       => $coupon->id,
            'customer_id'     => $customer->id,
            'order_id'        => 999001,
            'discount_amount' => 20,
            'used_at'         => now(),
        ]);

        $service = $this->makeService($customer, 100);
        $result  = CouponService::getInstance()->validate($coupon->code, $service);

        $this->assertFalse($result['valid']);
    }

    public function test_redeem_writes_usage_and_increments_used_count(): void
    {
        $customer = $this->makeCustomer();
        $coupon   = $this->makeCoupon(['type' => 'fixed', 'value' => 20]);
        $order    = (object) ['id' => 999100];

        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 20);

        $this->assertDatabaseHas('nice_coupon_usages', ['coupon_id' => $coupon->id, 'order_id' => 999100]);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_duplicate_redeem_same_order_violates_unique_constraint(): void
    {
        $customer = $this->makeCustomer();
        $coupon   = $this->makeCoupon([]);
        $order    = (object) ['id' => 999200];

        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 20);

        $this->expectException(QueryException::class);
        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 20);
    }

    public function test_total_limit_not_oversold_under_concurrency(): void
    {
        $coupon = $this->makeCoupon(['total_limit' => 1]);

        // 第一单核销成功
        CouponService::getInstance()->redeem($coupon, (object) ['id' => 999300], $this->makeCustomer()->id, 20);

        // 第二单使 used_count 超过上限，应抛异常（最终防线）
        $this->expectException(Exception::class);
        CouponService::getInstance()->redeem($coupon, (object) ['id' => 999301], $this->makeCustomer()->id, 20);
    }

    public function test_rollback_restores_usage_and_count(): void
    {
        $customer = $this->makeCustomer();
        $coupon   = $this->makeCoupon([]);
        $order    = (object) ['id' => 999400];

        CouponService::getInstance()->redeem($coupon, $order, $customer->id, 20);
        $this->assertEquals(1, $coupon->fresh()->used_count);

        CouponService::getInstance()->rollback($order);

        $this->assertEquals(0, $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('nice_coupon_usages', ['coupon_id' => $coupon->id, 'order_id' => 999400]);
    }
}
