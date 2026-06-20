<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->comment('Warehouse');
            $table->bigIncrements('id')->comment('ID');
            $table->string('code', 64)->unique()->comment('Warehouse Code');
            $table->string('name', 128)->comment('Warehouse Name');
            $table->text('description')->nullable()->comment('Description');
            $table->string('contact_name', 64)->default('')->comment('Contact Name');
            $table->string('contact_phone', 32)->default('')->comment('Contact Phone');
            $table->unsignedInteger('country_id')->default(0)->comment('Country ID');
            $table->string('country', 64)->default('')->comment('Country Name');
            $table->unsignedInteger('state_id')->default(0)->comment('State ID');
            $table->string('state', 64)->default('')->comment('State Name');
            $table->string('city', 64)->default('')->comment('City');
            $table->string('address_1')->default('')->comment('Address Line 1');
            $table->string('address_2')->default('')->comment('Address Line 2');
            $table->string('zipcode', 16)->default('')->comment('Zip Code');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Latitude');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Longitude');
            $table->integer('priority')->default(0)->comment('Priority (lower = higher)');
            $table->boolean('is_default')->default(false)->comment('Default Warehouse');
            $table->boolean('active')->default(true)->comment('Active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->comment('Warehouse Stock');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('warehouse_id')->index('ws_warehouse_id')->comment('Warehouse ID');
            $table->unsignedBigInteger('product_id')->default(0)->index('ws_product_id')->comment('Product ID');
            $table->unsignedBigInteger('sku_id')->default(0)->index('ws_sku_id')->comment('SKU ID');
            $table->string('sku_code', 128)->index('ws_sku_code')->comment('SKU Code');
            $table->integer('quantity')->default(0)->comment('Available Quantity');
            $table->integer('reserved_quantity')->default(0)->comment('Reserved Quantity');
            $table->integer('low_stock_threshold')->default(0)->comment('Low Stock Alert Threshold');
            $table->timestamps();
            $table->unique(['warehouse_id', 'sku_code'], 'ws_warehouse_sku');
        });

        Schema::create('warehouse_stock_movements', function (Blueprint $table) {
            $table->comment('Warehouse Stock Movement');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('warehouse_id')->index('wsm_warehouse_id')->comment('Warehouse ID');
            $table->string('sku_code', 128)->index('wsm_sku_code')->comment('SKU Code');
            $table->integer('quantity')->comment('Quantity Change (+/-)');
            $table->string('type', 32)->comment('Movement Type');
            $table->string('reference_type', 64)->default('')->comment('Reference Type');
            $table->unsignedBigInteger('reference_id')->default(0)->comment('Reference ID');
            $table->string('note', 500)->default('')->comment('Note');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('Operator Admin ID');
            $table->timestamps();
            $table->index(['reference_type', 'reference_id'], 'wsm_reference');
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->comment('Stock Transfer');
            $table->bigIncrements('id')->comment('ID');
            $table->string('number', 32)->unique()->comment('Transfer Number');
            $table->unsignedBigInteger('from_warehouse_id')->index('st_from_wh')->comment('Source Warehouse');
            $table->unsignedBigInteger('to_warehouse_id')->index('st_to_wh')->comment('Destination Warehouse');
            $table->string('status', 32)->default('pending')->index('st_status')->comment('Status');
            $table->string('note', 500)->default('')->comment('Note');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('Creator Admin ID');
            $table->timestamp('shipped_at')->nullable()->comment('Shipped At');
            $table->timestamp('completed_at')->nullable()->comment('Completed At');
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->comment('Stock Transfer Item');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('stock_transfer_id')->index('sti_transfer_id')->comment('Transfer ID');
            $table->string('sku_code', 128)->comment('SKU Code');
            $table->integer('quantity')->comment('Transfer Quantity');
            $table->integer('received_quantity')->default(0)->comment('Received Quantity');
            $table->timestamps();
        });

        Schema::create('order_shipment_items', function (Blueprint $table) {
            $table->comment('Order Shipment Item');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('shipment_id')->index('osi_shipment_id')->comment('Shipment ID');
            $table->unsignedBigInteger('order_item_id')->index('osi_order_item_id')->comment('Order Item ID');
            $table->string('sku_code', 128)->comment('SKU Code');
            $table->integer('quantity')->comment('Shipped Quantity');
            $table->timestamps();
        });

        Schema::table('order_shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->default(0)->after('order_id')->comment('Warehouse ID');
            $table->string('warehouse_name', 128)->default('')->after('warehouse_id')->comment('Warehouse Name Snapshot');
            $table->string('status', 32)->default('pending')->after('express_number')->comment('Shipment Status');
            $table->timestamp('shipped_at')->nullable()->after('status')->comment('Shipped At');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at')->comment('Delivered At');
        });
    }

    public function down(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->dropColumn(['warehouse_id', 'warehouse_name', 'status', 'shipped_at', 'delivered_at']);
        });
        Schema::dropIfExists('order_shipment_items');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('warehouse_stock_movements');
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('warehouses');
    }
};
