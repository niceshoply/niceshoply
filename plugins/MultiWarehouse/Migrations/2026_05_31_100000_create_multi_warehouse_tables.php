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
        if (! Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 32)->unique();
                $table->string('province', 64)->nullable();
                $table->string('city', 64)->nullable();
                $table->string('address', 255)->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('warehouse_stocks')) {
            Schema::create('warehouse_stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('sku_id')->index();
                $table->integer('quantity')->default(0);
                $table->timestamps();
                $table->unique(['warehouse_id', 'sku_id']);
            });
        }

        if (! Schema::hasTable('warehouse_transfers')) {
            Schema::create('warehouse_transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('from_warehouse_id');
                $table->unsignedBigInteger('to_warehouse_id');
                $table->unsignedBigInteger('sku_id');
                $table->integer('quantity');
                $table->string('remark', 255)->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfers');
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('warehouses');
    }
};
