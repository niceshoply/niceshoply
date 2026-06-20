<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Common\Services\WarehouseStockService;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Default to non-warehouse mode for standard order tests
        config()->set('nice.system.warehouse_enabled', false);
    }

    private function createOrderWithItems(string $status, int $itemCount = 1, int $qtyPerItem = 2): array
    {
        $order = Order::query()->create([
            'number'                 => 'LIFE-'.uniqid(),
            'customer_id'            => 0,
            'customer_group_id'      => 0,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Lifecycle Test',
            'email'                  => 'lifecycle@example.com',
            'calling_code'           => 1,
            'telephone'              => '5551234567',
            'total'                  => 0,
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => '1.0000',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'status'                 => $status,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => 'Test',
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '5551234567',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'CA',
            'shipping_city'          => 'LA',
            'shipping_address_1'     => '456 Test Ave',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '90001',
            'billing_method_code'    => 'stripe',
            'billing_method_name'    => 'Stripe',
            'billing_customer_name'  => 'Test',
            'billing_calling_code'   => '1',
            'billing_telephone'      => '5551234567',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'CA',
            'billing_city'           => 'LA',
            'billing_address_1'      => '456 Test Ave',
            'billing_address_2'      => '',
            'billing_zipcode'        => '90001',
        ]);

        $skus = [];
        for ($i = 0; $i < $itemCount; $i++) {
            $product = Product::query()->create([
                'active'   => true, 'position' => 0, 'sales' => 0,
                'price'    => 10 * ($i + 1), 'origin_price' => 10 * ($i + 1),
                'brand_id' => 0, 'tax_class_id' => 0,
                'weight'   => 0, 'weight_class' => '',
                'slug'     => 'life-'.uniqid('', true),
                'spu_code' => 'LIFE-SPU-'.uniqid('', true),
            ]);
            $sku = Sku::query()->create([
                'product_id'   => $product->id,
                'code'         => 'LIFE-SKU-'.uniqid(),
                'price'        => 10 * ($i + 1),
                'origin_price' => 10 * ($i + 1),
                'quantity'     => 100,
                'is_default'   => true,
                'position'     => 0,
            ]);
            Item::query()->create([
                'order_id'      => $order->id,
                'product_id'    => $product->id,
                'order_number'  => $order->number,
                'product_sku'   => $sku->code,
                'variant_label' => '',
                'name'          => 'Product '.($i + 1),
                'image'         => '',
                'quantity'      => $qtyPerItem,
                'price'         => 10 * ($i + 1),
            ]);
            $skus[] = ['product' => $product, 'sku' => $sku];
        }

        return ['order' => $order, 'skus' => $skus];
    }

    // ─── Full Order Lifecycle: Created → Unpaid → Paid → Shipped → Completed ───

    public function test_full_order_lifecycle(): void
    {
        $data  = $this->createOrderWithItems(StateMachineService::CREATED, 2, 3);
        $order = $data['order'];
        $skus  = $data['skus'];

        // 1. Created → Unpaid
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID);
        $order->refresh();
        $this->assertEquals(StateMachineService::UNPAID, $order->status);

        // 2. Unpaid → Paid (triggers stock deduction + sales update)
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        $order->refresh();
        $this->assertEquals(StateMachineService::PAID, $order->status);

        // Verify stock deducted for each SKU
        foreach ($skus as $item) {
            $item['sku']->refresh();
            $this->assertEquals(97, $item['sku']->quantity); // 100 - 3
        }

        // Verify sales incremented
        foreach ($skus as $item) {
            $item['product']->refresh();
            $this->assertEquals(3, $item['product']->sales);
        }

        // 3. Paid → Shipped
        $sm = StateMachineService::getInstance($order);
        $sm->setShipment([
            'express_code'    => 'fedex',
            'express_company' => 'FedEx',
            'express_number'  => 'FX'.uniqid(),
        ]);
        $sm->changeStatus(StateMachineService::SHIPPED);
        $order->refresh();
        $this->assertEquals(StateMachineService::SHIPPED, $order->status);

        // 4. Shipped → Completed
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::COMPLETED);
        $order->refresh();
        $this->assertEquals(StateMachineService::COMPLETED, $order->status);

        // Verify full history chain
        $histories = Order\History::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->pluck('status')
            ->toArray();
        $this->assertEquals([
            StateMachineService::UNPAID,
            StateMachineService::PAID,
            StateMachineService::SHIPPED,
            StateMachineService::COMPLETED,
        ], $histories);
    }

    // ─── Order Cancellation After Payment ───

    public function test_cancel_paid_order(): void
    {
        $data  = $this->createOrderWithItems(StateMachineService::UNPAID, 1, 5);
        $order = $data['order'];
        $sku   = $data['skus'][0]['sku'];

        // Pay
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        $sku->refresh();
        $this->assertEquals(95, $sku->quantity); // 100 - 5

        // Cancel
        $order->refresh();
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::CANCELLED, 'Refund requested');
        $order->refresh();
        $this->assertEquals(StateMachineService::CANCELLED, $order->status);

        // Note: stock is NOT restored on cancel in non-warehouse mode (by design)
        // The subStock deduction is permanent; refund is handled separately
    }

    // ─── Warehouse Mode: Reserve → Commit Lifecycle ───

    public function test_warehouse_stock_reserve_and_commit_lifecycle(): void
    {
        $warehouse = Warehouse::query()->create([
            'code'   => 'LIFE-WH-'.uniqid(),
            'name'   => 'Lifecycle Warehouse',
            'active' => true,
        ]);

        $data         = $this->createOrderWithItems(StateMachineService::PAID, 1, 10);
        $sku          = $data['skus'][0]['sku'];
        $stockService = WarehouseStockService::getInstance();

        // Setup warehouse stock
        $stockService->addStock($warehouse->id, $sku->code, 50);

        // Simulate checkout reservation
        $stockService->reserveStock($warehouse->id, $sku->code, 10, 'order', $data['order']->id);

        $stock = Stock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('sku_code', $sku->code)
            ->first();
        $this->assertEquals(50, $stock->quantity);
        $this->assertEquals(10, $stock->reserved_quantity);
        $this->assertEquals(40, $stock->available_quantity);

        // Simulate shipment commit
        $stockService->commitReservedStock($warehouse->id, $sku->code, 10, 'order', $data['order']->id);

        $stock->refresh();
        $this->assertEquals(40, $stock->quantity);
        $this->assertEquals(0, $stock->reserved_quantity);
        $this->assertEquals(40, $stock->available_quantity);

        // SKU total synced
        $sku->refresh();
        $this->assertEquals(40, $sku->quantity);
    }

    // ─── Warehouse Mode: Reserve → Cancel (Release) ───

    public function test_warehouse_stock_reserve_and_release_on_cancel(): void
    {
        $warehouse = Warehouse::query()->create([
            'code'   => 'CANCEL-WH-'.uniqid(),
            'name'   => 'Cancel Test Warehouse',
            'active' => true,
        ]);

        $data         = $this->createOrderWithItems(StateMachineService::UNPAID, 1, 8);
        $sku          = $data['skus'][0]['sku'];
        $stockService = WarehouseStockService::getInstance();

        // Setup and reserve
        $stockService->addStock($warehouse->id, $sku->code, 100);
        $stockService->reserveStock($warehouse->id, $sku->code, 8, 'order', $data['order']->id);

        $stock = Stock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('sku_code', $sku->code)
            ->first();
        $this->assertEquals(92, $stock->available_quantity);

        // Release on cancellation
        $stockService->releaseStock($warehouse->id, $sku->code, 8, 'order', $data['order']->id);

        $stock->refresh();
        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(0, $stock->reserved_quantity);
        $this->assertEquals(100, $stock->available_quantity);
    }

    // ─── Multiple Items Stock Deduction ───

    public function test_multiple_items_stock_deducted_correctly(): void
    {
        $data  = $this->createOrderWithItems(StateMachineService::UNPAID, 3, 4);
        $order = $data['order'];

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);

        // Each SKU started at 100, ordered 4 each
        foreach ($data['skus'] as $item) {
            $item['sku']->refresh();
            $this->assertEquals(96, $item['sku']->quantity);
            $item['product']->refresh();
            $this->assertEquals(4, $item['product']->sales);
        }
    }

    // ─── Partially Shipped Flow ───

    public function test_partially_shipped_to_shipped_to_completed(): void
    {
        $data  = $this->createOrderWithItems(StateMachineService::PAID, 2, 1);
        $order = $data['order'];

        // First partial shipment
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PARTIALLY_SHIPPED);
        $order->refresh();
        $this->assertEquals(StateMachineService::PARTIALLY_SHIPPED, $order->status);

        // All items shipped
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::SHIPPED);
        $order->refresh();
        $this->assertEquals(StateMachineService::SHIPPED, $order->status);

        // Complete
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::COMPLETED);
        $order->refresh();
        $this->assertEquals(StateMachineService::COMPLETED, $order->status);
    }
}
