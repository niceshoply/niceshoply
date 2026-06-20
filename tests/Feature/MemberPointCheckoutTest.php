<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\CustomerPoint;
use NiceShoply\Common\Models\MemberLevel;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Fee\Subtotal;
use NiceShoply\Common\Services\Member\MemberLevelService;
use NiceShoply\Common\Services\Member\PointService;
use ReflectionClass;
use Tests\TestCase;

/**
 * 会员折扣 + 积分抵现结账集成测试。
 */
class MemberPointCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nice.system.points_enabled'            => true,
            'nice.system.points_earn_rate'          => 1,
            'nice.system.points_redeem_rate'        => 100,
            'nice.system.points_max_redeem_percent' => 50,
            'nice.system.points_expire_days'        => 0,
        ]);
    }

    private function makeCustomer(array $extra = []): Customer
    {
        return Customer::query()->create(array_merge([
            'email'             => 'member-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'Member Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ], $extra))->refresh();
    }

    private function makeCheckoutService(Customer $customer, float $subtotal): CheckoutService
    {
        $service = CheckoutService::getInstance($customer->id, 'member-guest-'.uniqid());

        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('cartList');
        $prop->setAccessible(true);
        $prop->setValue($service, [
            [
                'subtotal'     => $subtotal,
                'quantity'     => 1,
                'price'        => $subtotal,
                'is_virtual'   => false,
                'weight'       => 1,
                'tax_class_id' => 0,
            ],
        ]);

        return $service;
    }

    public function test_member_discount_applied_via_price_hook(): void
    {
        $level = MemberLevel::query()->create([
            'name'             => 'Gold',
            'threshold_type'   => 'amount',
            'threshold_value'  => 0,
            'discount_percent' => 10,
            'free_shipping'    => false,
            'priority'         => 10,
            'active'           => true,
        ]);

        $customer = $this->makeCustomer(['member_level_id' => $level->id]);
        $this->actingAs($customer, 'customer');

        $result = MemberLevelService::getInstance()->applyMemberPrice([
            'sku'   => null,
            'price' => 100,
        ]);

        $this->assertEquals(90.0, (float) $result['price']);
    }

    public function test_points_fee_enters_checkout_total(): void
    {
        $customer = $this->makeCustomer();
        CustomerPoint::query()->create([
            'customer_id'  => $customer->id,
            'balance'      => 5000,
            'total_earned' => 5000,
            'total_spent'  => 0,
        ]);

        $service = $this->makeCheckoutService($customer, 200);
        (new Subtotal($service))->addFee();

        $result = PointService::getInstance()->validateRedeem($service, 5000);
        $this->assertTrue($result['valid']);
        $this->assertEquals(5000, $result['points']);
        $this->assertEquals(50.0, $result['amount']);

        $service->addFeeList([
            'code'      => 'points',
            'title'     => trans('front/point.fee_title', ['points' => $result['points']]),
            'total'     => -$result['amount'],
            'reference' => ['points' => $result['points']],
        ]);

        $this->assertEquals(150.0, $service->getAmount());
    }

    public function test_point_validate_respects_balance_cap(): void
    {
        $customer = $this->makeCustomer();
        CustomerPoint::query()->create([
            'customer_id'  => $customer->id,
            'balance'      => 100,
            'total_earned' => 100,
            'total_spent'  => 0,
        ]);

        $service = $this->makeCheckoutService($customer, 1000);
        $result  = PointService::getInstance()->validateRedeem($service, 10000);

        $this->assertFalse($result['valid']);
    }

    public function test_member_free_shipping_flag(): void
    {
        $level = MemberLevel::query()->create([
            'name'             => 'VIP',
            'threshold_type'   => 'amount',
            'threshold_value'  => 0,
            'discount_percent' => 0,
            'free_shipping'    => true,
            'priority'         => 5,
            'active'           => true,
        ]);

        $customer = $this->makeCustomer(['member_level_id' => $level->id]);

        $this->assertTrue(MemberLevelService::getInstance()->customerHasFreeShipping($customer->id));
    }
}
