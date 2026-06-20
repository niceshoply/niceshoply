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
        if (! Schema::hasTable('virtual_goods')) {
            Schema::create('virtual_goods', function (Blueprint $table) {
                $table->id();
                $table->string('product_sku', 64)->unique();
                $table->string('name')->nullable();
                $table->string('type', 16)->default('card'); // card / text
                $table->text('fixed_content')->nullable();    // type=text 时发放固定内容
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('virtual_cards')) {
            Schema::create('virtual_cards', function (Blueprint $table) {
                $table->id();
                $table->string('product_sku', 64)->index();
                $table->text('content');
                $table->string('status', 16)->default('unused'); // unused / used
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('order_item_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->dateTime('used_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('virtual_deliveries')) {
            Schema::create('virtual_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('order_item_id')->index();
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->string('product_sku', 64)->index();
                $table->text('content');
                $table->timestamps();
                $table->unique(['order_item_id'], 'uniq_delivery_item');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_deliveries');
        Schema::dropIfExists('virtual_cards');
        Schema::dropIfExists('virtual_goods');
    }
};
