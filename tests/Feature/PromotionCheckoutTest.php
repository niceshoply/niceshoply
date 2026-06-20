<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Promotion;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Fee\Discount;
use NiceShoply\Common\Services\Promotion\PromotionService;
use ReflectionClass;
use Tests\TestCase;

/**
 * 促销引擎结账集成测试。
 *
 * 通过反射注入购物车，隔离地验证满减/百分比/阶梯/免运费/封顶/互斥的折扣计算，
 * 以及折扣项经 Fee\Discount 进入 feeList 的金额闭环。
 */
class PromotionCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'promo-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'Promo Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();
    }

    private function makeService(Customer $customer, float $subtotal, int $qty = 1): CheckoutService
    {
        $service = CheckoutService::getInstance($customer->id, 'promo-guest-'.uniqid());

        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('cartList');
        $prop->setAccessible(true);
        $prop->setValue($service, [
            ['subtotal' => $subtotal, 'quantity' => $qty, 'price' => $subtotal / max(1, $qty), 'is_virtual' => false, 'weight' => 1, 'tax_class_id' => 0],
        ]);

        return $service;
    }

    private function makePromotion(array $attributes): Promotion
    {
        return Promotion::query()->create(array_merge([
            'name'           => 'P-'.uniqid(),
            'scope'          => 'cart',
            'condition_type' => 'none',
            'conditions'     => [],
            'action_type'    => 'fixed',
            'actions'        => [],
            'priority'       => 0,
            'exclusive'      => false,
            'active'         => true,
        ], $attributes));
    }

    public function test_min_amount_fixed_discount(): void
    {
        $this->makePromotion([
            'condition_type' => 'min_amount',
            'conditions'     => ['min_amount' => 200],
            'action_type'    => 'fixed',
            'actions'        => ['value' => 30],
        ]);

        $service = $this->makeService($this->makeCustomer(), 200);
        $entries = PromotionService::getInstance()->calculate($service);

        $this->assertCount(1, $entries);
        $this->assertEquals(30, $entries[0]['amount']);
    }

    public function test_min_amount_not_met_yields_no_discount(): void
    {
        $this->makePromotion([
            'condition_type' => 'min_amount',
            'conditions'     => ['min_amount' => 200],
            'action_type'    => 'fixed',
            'actions'        => ['value' => 30],
        ]);

        $service = $this->makeService($this->makeCustomer(), 150);
        $entries = PromotionService::getInstance()->calculate($service);

        $this->assertCount(0, $entries);
    }

    public function test_percent_discount_with_cap(): void
    {
        $this->makePromotion([
            'action_type' => 'percent',
            'actions'     => ['value' => 10, 'max' => 15],
        ]);

        $service = $this->makeService($this->makeCustomer(), 200);
        $entries = PromotionService::getInstance()->calculate($service);

        // 10% of 200 = 20，封顶 15
        $this->assertEquals(15, $entries[0]['amount']);
    }

    public function test_tiered_discount_picks_highest_matched_tier(): void
    {
        $this->makePromotion([
            'condition_type' => 'tiered',
            'conditions'     => ['tiers' => [['min' => 100, 'value' => 10], ['min' => 300, 'value' => 40]]],
            'action_type'    => 'fixed',
            'actions'        => ['tiers' => [['min' => 100, 'value' => 10], ['min' => 300, 'value' => 40]]],
        ]);

        $low  = PromotionService::getInstance()->calculate($this->makeService($this->makeCustomer(), 200));
        $high = PromotionService::getInstance()->calculate($this->makeService($this->makeCustomer(), 350));

        $this->assertEquals(10, $low[0]['amount']);
        $this->assertEquals(40, $high[0]['amount']);
    }

    public function test_free_shipping_marks_checkout(): void
    {
        $this->makePromotion([
            'action_type' => 'free_shipping',
        ]);

        $service = $this->makeService($this->makeCustomer(), 120);
        (new Discount($service))->addFee();

        $this->assertTrue($service->isFreeShipping());
    }

    public function test_discount_capped_to_subtotal(): void
    {
        $this->makePromotion([
            'action_type' => 'fixed',
            'actions'     => ['value' => 100],
        ]);

        $service = $this->makeService($this->makeCustomer(), 50);
        $entries = PromotionService::getInstance()->calculate($service);

        // 固定减 100，但小计仅 50，封顶 50
        $this->assertEquals(50, $entries[0]['amount']);
    }

    public function test_exclusive_promotion_stops_stacking(): void
    {
        // 高优先级互斥活动 + 低优先级普通活动，最终仅命中互斥活动
        $this->makePromotion([
            'action_type' => 'fixed',
            'actions'     => ['value' => 30],
            'priority'    => 100,
            'exclusive'   => true,
        ]);
        $this->makePromotion([
            'action_type' => 'fixed',
            'actions'     => ['value' => 10],
            'priority'    => 1,
        ]);

        $service = $this->makeService($this->makeCustomer(), 500);
        $entries = PromotionService::getInstance()->calculate($service);

        $this->assertCount(1, $entries);
        $this->assertEquals(30, $entries[0]['amount']);
    }

    public function test_discount_enters_fee_list_as_negative(): void
    {
        $this->makePromotion([
            'action_type' => 'fixed',
            'actions'     => ['value' => 25],
        ]);

        $service = $this->makeService($this->makeCustomer(), 100);
        (new Discount($service))->addFee();

        $ref  = new ReflectionClass($service);
        $prop = $ref->getProperty('feeList');
        $prop->setAccessible(true);
        $feeList = $prop->getValue($service);

        $discountFee = collect($feeList)->firstWhere('code', 'discount');
        $this->assertNotNull($discountFee);
        $this->assertEquals(-25, $discountFee['total']);
    }
}
