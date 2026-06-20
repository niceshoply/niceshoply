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
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('contact', 64)->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('email', 128)->nullable();
                $table->string('address', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number', 32)->unique();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('warehouse_id')->default(0);
                $table->string('status', 16)->default('draft'); // draft/ordered/received/cancelled
                $table->decimal('total', 12, 2)->default(0);
                $table->string('remark', 255)->nullable();
                $table->timestamp('ordered_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->unsignedBigInteger('sku_id')->index();
                $table->unsignedInteger('quantity');
                $table->unsignedInteger('received_qty')->default(0);
                $table->decimal('cost_price', 12, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
