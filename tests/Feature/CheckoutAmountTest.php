<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\Common\Services\Fee\Subtotal;
use ReflectionClass;
use Tests\TestCase;

/**
 * Checkout 金额计算测试：小计、商品数量、运费/税/余额费用聚合与四舍五入。
 *
 * 通过反射注入购物车 / 费用清单，隔离地验证 CheckoutService 的金额数学，
 * 不依赖完整的地址 / 配送 / 库存初始化，结果稳定可重复。
 */
class CheckoutAmountTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(float $balance = 0): Customer
    {
        $customer = Customer::query()->create([
            'email'             => 'checkout-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'Checkout Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ]);

        if ($balance > 0) {
            $customer->balance = $balance;
            $customer->save();
        }

        return $customer->refresh();
    }

    private function makeService(Customer $customer): CheckoutService
    {
        // 传入 guestID 以避免依赖会话生成访客 ID
        return CheckoutService::getInstance($customer->id, 'test-guest-'.uniqid());
    }

    private function setProtected(object $target, string $property, mixed $value): void
    {
        $ref  = new ReflectionClass($target);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($target, $value);
    }

    public function test_subtotal_sums_cart_item_subtotals(): void
    {
        $service = $this->makeService($this->makeCustomer());

        $this->setProtected($service, 'cartList', [
            ['subtotal' => 19.99, 'quantity' => 1, 'price' => 19.99, 'is_virtual' => false, 'weight' => 1, 'tax_class_id' => 0],
            ['subtotal' => 40.02, 'quantity' => 2, 'price' => 20.01, 'is_virtual' => false, 'weight' => 2, 'tax_class_id' => 0],
        ]);

        $this->assertEquals(60.01, (new Subtotal($service))->getSubtotal());
        $this->assertEquals(60.01, $service->getSubTotal());
    }

    public function test_total_number_sums_quantities(): void
    {
        $service = $this->makeService($this->makeCustomer());

        $this->setProtected($service, 'cartList', [
            ['subtotal' => 10, 'quantity' => 3, 'price' => 10, 'is_virtual' => false, 'weight' => 0, 'tax_class_id' => 0],
            ['subtotal' => 10, 'quantity' => 5, 'price' => 10, 'is_virtual' => false, 'weight' => 0, 'tax_class_id' => 0],
        ]);

        $this->assertEquals(8, $service->getTotalNumber());
    }

    public function test_amount_aggregates_subtotal_shipping_tax_and_balance_deduction(): void
    {
        $service = $this->makeService($this->makeCustomer());

        // 模拟费用引擎产出的费用清单：小计 + 运费 + 税 - 余额抵扣
        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 100.00],
            ['code' => 'shipping', 'title' => 'Shipping', 'total' => 12.50],
            ['code' => 'tax', 'title' => 'Tax', 'total' => 8.80],
            ['code' => 'balance', 'title' => 'Balance', 'total' => -20.00],
        ]);

        // 100 + 12.5 + 8.8 - 20 = 101.30
        $this->assertEquals(101.30, $service->getAmount());
    }

    public function test_amount_rounds_to_currency_decimal_places(): void
    {
        $service = $this->makeService($this->makeCustomer());

        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 33.333],
            ['code' => 'tax', 'title' => 'Tax', 'total' => 0.334],
        ]);

        // 33.333 + 0.334 = 33.667 → 四舍五入到 2 位 = 33.67
        $this->assertEquals(33.67, $service->getAmount());
    }

    public function test_amount_with_coupon_discount_combined_with_shipping(): void
    {
        $service = $this->makeService($this->makeCustomer());

        // 小计 + 运费 - 优惠券
        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 200.00],
            ['code' => 'shipping', 'title' => 'Shipping', 'total' => 15.00],
            ['code' => 'coupon', 'title' => 'Coupon', 'total' => -30.00],
        ]);

        // 200 + 15 - 30 = 185.00
        $this->assertEquals(185.00, $service->getAmount());
    }

    public function test_amount_with_full_combination_of_shipping_tax_coupon_and_balance(): void
    {
        $service = $this->makeService($this->makeCustomer(50));

        // 小计 + 运费 + 税 - 优惠券 - 余额抵扣（完整组合）
        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 300.00],
            ['code' => 'shipping', 'title' => 'Shipping', 'total' => 18.00],
            ['code' => 'tax', 'title' => 'Tax', 'total' => 25.44],
            ['code' => 'coupon', 'title' => 'Coupon', 'total' => -40.00],
            ['code' => 'balance', 'title' => 'Balance', 'total' => -50.00],
        ]);

        // 300 + 18 + 25.44 - 40 - 50 = 253.44
        $this->assertEquals(253.44, $service->getAmount());
    }

    public function test_amount_with_free_shipping_promotion(): void
    {
        $service = $this->makeService($this->makeCustomer());

        // 免运费场景：运费项为 0，不应影响总额
        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 120.00],
            ['code' => 'shipping', 'title' => 'Free Shipping', 'total' => 0.00],
            ['code' => 'tax', 'title' => 'Tax', 'total' => 9.60],
        ]);

        // 120 + 0 + 9.6 = 129.60
        $this->assertEquals(129.60, $service->getAmount());
    }

    public function test_amount_with_coupon_exceeding_subtotal_yields_minimal_total(): void
    {
        $service = $this->makeService($this->makeCustomer());

        // 优惠券大于小计 + 运费的极端组合：仅校验金额数学（求和后四舍五入）
        $this->setProtected($service, 'feeList', [
            ['code' => 'subtotal', 'title' => 'Subtotal', 'total' => 50.00],
            ['code' => 'shipping', 'title' => 'Shipping', 'total' => 8.00],
            ['code' => 'coupon', 'title' => 'Coupon', 'total' => -55.00],
        ]);

        // 50 + 8 - 55 = 3.00
        $this->assertEquals(3.00, $service->getAmount());
    }

    public function test_balance_amount_reads_customer_balance(): void
    {
        $service = $this->makeService($this->makeCustomer(75.5));

        $this->assertEquals(75.5, $service->getBalanceAmount());
    }

    public function test_check_is_virtual_reflects_cart_items(): void
    {
        $service = $this->makeService($this->makeCustomer());

        $this->setProtected($service, 'cartList', [
            ['subtotal' => 10, 'quantity' => 1, 'price' => 10, 'is_virtual' => true, 'weight' => 0, 'tax_class_id' => 0],
        ]);
        $this->assertTrue($service->checkIsVirtual());

        $mixed = $this->makeService($this->makeCustomer());
        $this->setProtected($mixed, 'cartList', [
            ['subtotal' => 10, 'quantity' => 1, 'price' => 10, 'is_virtual' => true, 'weight' => 0, 'tax_class_id' => 0],
            ['subtotal' => 10, 'quantity' => 1, 'price' => 10, 'is_virtual' => false, 'weight' => 1, 'tax_class_id' => 0],
        ]);
        $this->assertFalse($mixed->checkIsVirtual());
    }
}
