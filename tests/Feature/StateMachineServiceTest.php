<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Services\StateMachineService;
use Tests\TestCase;

class StateMachineServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure warehouse mode is off so subStock and addShipment run normally
        config()->set('nice.system.warehouse_enabled', false);
    }

    private function createOrder(string $status = StateMachineService::CREATED, array $extra = []): Order
    {
        return Order::query()->create(array_merge([
            'number'                 => 'TEST-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Test Customer',
            'email'                  => 'test@example.com',
            'calling_code'           => 1,
            'telephone'              => '1234567890',
            'total'                  => 100.00,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => $status,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => 'Test Customer',
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
            'billing_method_code'    => 'stripe',
            'billing_method_name'    => 'Stripe',
            'billing_customer_name'  => 'Test Customer',
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
        ], $extra));
    }

    // ─── Valid Transitions ───

    public function test_created_to_unpaid(): void
    {
        $order = $this->createOrder(StateMachineService::CREATED);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::UNPAID);

        $order->refresh();
        $this->assertEquals(StateMachineService::UNPAID, $order->status);
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status'   => StateMachineService::UNPAID,
        ]);
    }

    public function test_unpaid_to_paid(): void
    {
        $order = $this->createOrder(StateMachineService::UNPAID);

        // Create a product + SKU + order item so subStock can run
        $product = Product::query()->create([
            'active'       => true, 'position' => 0, 'sales' => 0, 'price' => 10,
            'origin_price' => 10, 'brand_id' => 0, 'tax_class_id' => 0,
            'weight'       => 0, 'weight_class' => '',
        ]);
        $sku = Sku::query()->create([
            'product_id' => $product->id, 'code' => 'TEST-SKU-'.uniqid(),
            'price'      => 10, 'origin_price' => 10, 'quantity' => 50,
            'is_default' => true, 'position' => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id,
            'product_id'    => $product->id,
            'order_number'  => $order->number,
            'product_sku'   => $sku->code,
            'variant_label' => '',
            'name'          => 'Test Product',
            'image'         => '',
            'quantity'      => 2,
            'price'         => 10,
        ]);

        $sm = StateMachineService::getInstance($order);
        $sm->changeStatus(StateMachineService::PAID);

        $order->refresh();
        $this->assertEquals(StateMachineService::PAID, $order->status);

        // Verify sales incremented
        $product->refresh();
        $this->assertEquals(2, $product->sales);
    }

    public function test_unpaid_to_cancelled(): void
    {
        $order = $this->createOrder(StateMachineService::UNPAID);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::CANCELLED, 'Customer requested');

        $order->refresh();
        $this->assertEquals(StateMachineService::CANCELLED, $order->status);
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status'   => StateMachineService::CANCELLED,
            'comment'  => 'Customer requested',
        ]);
    }

    public function test_paid_to_shipped(): void
    {
        $order = $this->createOrder(StateMachineService::PAID);
        $sm    = StateMachineService::getInstance($order);

        $sm->setShipment([
            'express_code'    => 'ups',
            'express_company' => 'UPS',
            'express_number'  => 'TRACK123',
        ]);
        $sm->changeStatus(StateMachineService::SHIPPED);

        $order->refresh();
        $this->assertEquals(StateMachineService::SHIPPED, $order->status);
        $this->assertDatabaseHas('order_shipments', [
            'order_id'       => $order->id,
            'express_number' => 'TRACK123',
        ]);
    }

    public function test_paid_to_partially_shipped(): void
    {
        $order = $this->createOrder(StateMachineService::PAID);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::PARTIALLY_SHIPPED);

        $order->refresh();
        $this->assertEquals(StateMachineService::PARTIALLY_SHIPPED, $order->status);
    }

    public function test_shipped_to_completed(): void
    {
        $order = $this->createOrder(StateMachineService::SHIPPED);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::COMPLETED);

        $order->refresh();
        $this->assertEquals(StateMachineService::COMPLETED, $order->status);
    }

    public function test_paid_to_completed(): void
    {
        $order = $this->createOrder(StateMachineService::PAID);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::COMPLETED);

        $order->refresh();
        $this->assertEquals(StateMachineService::COMPLETED, $order->status);
    }

    public function test_paid_to_cancelled(): void
    {
        $order = $this->createOrder(StateMachineService::PAID);
        $sm    = StateMachineService::getInstance($order);

        $sm->changeStatus(StateMachineService::CANCELLED);

        $order->refresh();
        $this->assertEquals(StateMachineService::CANCELLED, $order->status);
    }

    // ─── Auto-complete for digital orders (no shipping method) ───

    public function test_digital_order_auto_completes_on_payment(): void
    {
        $order = $this->createOrder(StateMachineService::UNPAID, [
            'shipping_method_code' => '',
            'shipping_method_name' => '',
        ]);

        $product = Product::query()->create([
            'active'       => true, 'position' => 0, 'sales' => 0, 'price' => 10,
            'origin_price' => 10, 'brand_id' => 0, 'tax_class_id' => 0,
            'weight'       => 0, 'weight_class' => '',
        ]);
        $sku = Sku::query()->create([
            'product_id' => $product->id, 'code' => 'DIGITAL-SKU-'.uniqid(),
            'price'      => 10, 'origin_price' => 10, 'quantity' => 100,
            'is_default' => true, 'position' => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id, 'product_id' => $product->id,
            'order_number'  => $order->number, 'product_sku' => $sku->code,
            'variant_label' => '', 'name' => 'Digital Product',
            'image'         => '', 'quantity' => 1, 'price' => 10,
        ]);

        $sm = StateMachineService::getInstance($order);
        $sm->changeStatus(StateMachineService::PAID);

        $order->refresh();
        // Digital orders (no shipping_method_code) auto-complete after payment
        $this->assertEquals(StateMachineService::COMPLETED, $order->status);
    }

    // ─── Invalid Transitions ───

    public function test_cannot_transition_from_completed(): void
    {
        $order = $this->createOrder(StateMachineService::COMPLETED);
        $sm    = StateMachineService::getInstance($order);

        $this->expectException(\Exception::class);
        $sm->changeStatus(StateMachineService::PAID);
    }

    public function test_cannot_transition_from_cancelled(): void
    {
        $order = $this->createOrder(StateMachineService::CANCELLED);
        $sm    = StateMachineService::getInstance($order);

        $this->expectException(\Exception::class);
        $sm->changeStatus(StateMachineService::PAID);
    }

    public function test_cannot_skip_to_shipped_from_unpaid(): void
    {
        $order = $this->createOrder(StateMachineService::UNPAID);
        $sm    = StateMachineService::getInstance($order);

        $this->expectException(\Exception::class);
        $sm->changeStatus(StateMachineService::SHIPPED);
    }

    public function test_cannot_go_backwards_from_paid_to_unpaid(): void
    {
        $order = $this->createOrder(StateMachineService::PAID);
        $sm    = StateMachineService::getInstance($order);

        $this->expectException(\Exception::class);
        $sm->changeStatus(StateMachineService::UNPAID);
    }

    // ─── Next Statuses ───

    public function test_next_statuses_for_created(): void
    {
        $order    = $this->createOrder(StateMachineService::CREATED);
        $sm       = StateMachineService::getInstance($order);
        $statuses = collect($sm->nextBackendStatuses())->pluck('status')->toArray();

        $this->assertEquals([StateMachineService::UNPAID], $statuses);
    }

    public function test_next_statuses_for_paid(): void
    {
        $order    = $this->createOrder(StateMachineService::PAID);
        $sm       = StateMachineService::getInstance($order);
        $statuses = collect($sm->nextBackendStatuses())->pluck('status')->toArray();

        $this->assertContains(StateMachineService::CANCELLED, $statuses);
        $this->assertContains(StateMachineService::SHIPPED, $statuses);
        $this->assertContains(StateMachineService::COMPLETED, $statuses);
        $this->assertContains(StateMachineService::PARTIALLY_SHIPPED, $statuses);
    }

    public function test_next_statuses_for_completed_is_empty(): void
    {
        $order    = $this->createOrder(StateMachineService::COMPLETED);
        $sm       = StateMachineService::getInstance($order);
        $statuses = $sm->nextBackendStatuses();

        $this->assertEmpty($statuses);
    }

    // ─── History & Notification ───

    public function test_history_records_created_for_each_transition(): void
    {
        $order = $this->createOrder(StateMachineService::CREATED);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID);
        $order->refresh();

        $product = Product::query()->create([
            'active'       => true, 'position' => 0, 'sales' => 0, 'price' => 10,
            'origin_price' => 10, 'brand_id' => 0, 'tax_class_id' => 0,
            'weight'       => 0, 'weight_class' => '',
        ]);
        $sku = Sku::query()->create([
            'product_id' => $product->id, 'code' => 'HIST-SKU-'.uniqid(),
            'price'      => 10, 'origin_price' => 10, 'quantity' => 100,
            'is_default' => true, 'position' => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id, 'product_id' => $product->id,
            'order_number'  => $order->number, 'product_sku' => $sku->code,
            'variant_label' => '', 'name' => 'Test', 'image' => '',
            'quantity'      => 1, 'price' => 10,
        ]);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        $order->refresh();

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::COMPLETED);

        $histories = Order\History::query()->where('order_id', $order->id)->pluck('status')->toArray();
        $this->assertContains(StateMachineService::UNPAID, $histories);
        $this->assertContains(StateMachineService::PAID, $histories);
        $this->assertContains(StateMachineService::COMPLETED, $histories);
    }

    // ─── Stock Deduction on Payment ───

    public function test_sku_stock_deducted_on_payment(): void
    {
        $order = $this->createOrder(StateMachineService::UNPAID);

        $product = Product::query()->create([
            'active'       => true, 'position' => 0, 'sales' => 0, 'price' => 25,
            'origin_price' => 25, 'brand_id' => 0, 'tax_class_id' => 0,
            'weight'       => 0, 'weight_class' => '',
        ]);
        $sku = Sku::query()->create([
            'product_id' => $product->id, 'code' => 'STOCK-SKU-'.uniqid(),
            'price'      => 25, 'origin_price' => 25, 'quantity' => 30,
            'is_default' => true, 'position' => 0,
        ]);
        Item::query()->create([
            'order_id'      => $order->id, 'product_id' => $product->id,
            'order_number'  => $order->number, 'product_sku' => $sku->code,
            'variant_label' => '', 'name' => 'Stock Test Product',
            'image'         => '', 'quantity' => 5, 'price' => 25,
        ]);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);

        $sku->refresh();
        $this->assertEquals(25, $sku->quantity); // 30 - 5 = 25
    }
}
