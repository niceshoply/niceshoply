<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Models\Warehouse\StockMovement;
use NiceShoply\Common\Services\WarehouseStockService;
use Tests\TestCase;

class WarehouseStockServiceTest extends TestCase
{
    use DatabaseTransactions;

    private WarehouseStockService $service;

    private Warehouse $warehouse;

    private Sku $sku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service   = WarehouseStockService::getInstance();
        $this->warehouse = Warehouse::query()->create([
            'code'   => 'TEST-WH-'.uniqid(),
            'name'   => 'Test Warehouse',
            'active' => true,
        ]);
        $this->sku = Sku::query()->create([
            'product_id'   => 0,
            'code'         => 'TEST-SKU-'.uniqid(),
            'price'        => 10,
            'origin_price' => 10,
            'quantity'     => 0,
            'is_default'   => true,
            'position'     => 0,
        ]);
    }

    // ─── Add Stock ───

    public function test_add_stock_increases_quantity(): void
    {
        $stock = $this->service->addStock($this->warehouse->id, $this->sku->code, 100, 'Initial stock');

        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(0, $stock->reserved_quantity);
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'sku_code'     => $this->sku->code,
            'quantity'     => 100,
            'type'         => StockMovement::TYPE_INBOUND,
        ]);
    }

    public function test_add_stock_rejects_zero_quantity(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Quantity must be positive');

        $this->service->addStock($this->warehouse->id, $this->sku->code, 0);
    }

    public function test_add_stock_rejects_negative_quantity(): void
    {
        $this->expectException(Exception::class);

        $this->service->addStock($this->warehouse->id, $this->sku->code, -5);
    }

    public function test_add_stock_syncs_sku_total(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 50);

        $this->sku->refresh();
        $this->assertEquals(50, $this->sku->quantity);
    }

    // ─── Remove Stock ───

    public function test_remove_stock_decreases_quantity(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $stock = $this->service->removeStock($this->warehouse->id, $this->sku->code, 30);

        $this->assertEquals(70, $stock->quantity);
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'sku_code'     => $this->sku->code,
            'quantity'     => -30,
            'type'         => StockMovement::TYPE_OUTBOUND,
        ]);
    }

    public function test_remove_stock_fails_on_insufficient_quantity(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 10);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->service->removeStock($this->warehouse->id, $this->sku->code, 20);
    }

    // ─── Reserve Stock ───

    public function test_reserve_stock_increases_reserved_quantity(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $stock = $this->service->reserveStock($this->warehouse->id, $this->sku->code, 30);

        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(30, $stock->reserved_quantity);
        $this->assertEquals(70, $stock->available_quantity);
    }

    public function test_reserve_stock_fails_on_insufficient_available(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 50);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 40);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient available stock');

        // Only 10 available (50 - 40 reserved), trying to reserve 20
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 20);
    }

    // ─── Release Stock ───

    public function test_release_stock_decreases_reserved_quantity(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 30);
        $stock = $this->service->releaseStock($this->warehouse->id, $this->sku->code, 30);

        $this->assertEquals(100, $stock->quantity);
        $this->assertEquals(0, $stock->reserved_quantity);
        $this->assertEquals(100, $stock->available_quantity);
    }

    public function test_release_stock_caps_at_reserved_amount(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 20);

        // Release more than reserved — should cap at reserved_quantity
        $stock = $this->service->releaseStock($this->warehouse->id, $this->sku->code, 50);

        $this->assertEquals(0, $stock->reserved_quantity);
    }

    // ─── Commit Reserved Stock ───

    public function test_commit_reserved_stock_deducts_both_quantities(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 30);
        $stock = $this->service->commitReservedStock($this->warehouse->id, $this->sku->code, 30);

        $this->assertEquals(70, $stock->quantity);       // 100 - 30
        $this->assertEquals(0, $stock->reserved_quantity); // 30 - 30
    }

    public function test_commit_syncs_sku_total(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 25);
        $this->service->commitReservedStock($this->warehouse->id, $this->sku->code, 25);

        $this->sku->refresh();
        $this->assertEquals(75, $this->sku->quantity); // 100 - 25
    }

    // ─── Adjust Stock ───

    public function test_adjust_stock_positive(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 50);
        $stock = $this->service->adjustStock($this->warehouse->id, $this->sku->code, 20, 'Recount');

        $this->assertEquals(70, $stock->quantity);
        $this->assertDatabaseHas('warehouse_stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'sku_code'     => $this->sku->code,
            'quantity'     => 20,
            'type'         => StockMovement::TYPE_ADJUSTMENT,
        ]);
    }

    public function test_adjust_stock_negative(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 50);
        $stock = $this->service->adjustStock($this->warehouse->id, $this->sku->code, -10, 'Damaged');

        $this->assertEquals(40, $stock->quantity);
    }

    public function test_adjust_stock_rejects_negative_result(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 10);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('negative stock');

        $this->service->adjustStock($this->warehouse->id, $this->sku->code, -20);
    }

    // ─── Auto-create Stock Record ───

    public function test_auto_creates_stock_record_on_first_operation(): void
    {
        $newSku = Sku::query()->create([
            'product_id' => 0, 'code' => 'NEW-SKU-'.uniqid(),
            'price'      => 5, 'origin_price' => 5, 'quantity' => 0,
            'is_default' => true, 'position' => 0,
        ]);

        $stock = $this->service->addStock($this->warehouse->id, $newSku->code, 10);

        $this->assertInstanceOf(Stock::class, $stock);
        $this->assertEquals(10, $stock->quantity);
        $this->assertEquals($this->warehouse->id, $stock->warehouse_id);
    }

    // ─── Full Lifecycle ───

    public function test_full_stock_lifecycle(): void
    {
        // 1. Inbound
        $this->service->addStock($this->warehouse->id, $this->sku->code, 100);

        // 2. Reserve for order
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 20);
        $stock = Stock::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('sku_code', $this->sku->code)
            ->first();
        $this->assertEquals(80, $stock->available_quantity);

        // 3. Commit on shipment
        $this->service->commitReservedStock($this->warehouse->id, $this->sku->code, 20);
        $stock->refresh();
        $this->assertEquals(80, $stock->quantity);
        $this->assertEquals(0, $stock->reserved_quantity);

        // 4. Verify movement count
        $movementCount = StockMovement::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('sku_code', $this->sku->code)
            ->count();
        $this->assertEquals(3, $movementCount); // inbound + reservation + outbound(commit)
    }

    // ─── Reserve then Cancel (Release) ───

    public function test_reserve_then_cancel_restores_availability(): void
    {
        $this->service->addStock($this->warehouse->id, $this->sku->code, 50);
        $this->service->reserveStock($this->warehouse->id, $this->sku->code, 30);

        $stock = Stock::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->where('sku_code', $this->sku->code)
            ->first();
        $this->assertEquals(20, $stock->available_quantity);

        // Cancel order → release reservation
        $this->service->releaseStock($this->warehouse->id, $this->sku->code, 30);
        $stock->refresh();
        $this->assertEquals(50, $stock->available_quantity);
        $this->assertEquals(50, $stock->quantity); // quantity unchanged
    }
}
