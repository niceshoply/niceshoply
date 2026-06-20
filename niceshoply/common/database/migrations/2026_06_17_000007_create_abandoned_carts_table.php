<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nice_abandoned_carts')) {
            return;
        }

        Schema::create('nice_abandoned_carts', function (Blueprint $table) {
            $table->comment('弃购购物车快照与召回记录');
            $table->id();
            $table->string('cart_key', 64)->unique('nice_abandoned_carts_key_unique')->comment('c:{customer_id} 或 g:{guest_id}');
            $table->unsignedBigInteger('customer_id')->default(0)->index('nice_abandoned_carts_customer_idx');
            $table->string('guest_id', 64)->default('');
            $table->string('email')->default('')->comment('召回邮箱快照');
            $table->json('cart_snapshot')->comment('购物车商品快照');
            $table->decimal('cart_total', 15, 4)->default(0);
            $table->string('currency_code', 16)->default('');
            $table->unsignedBigInteger('coupon_id')->default(0)->comment('附赠召回券');
            $table->string('coupon_code', 64)->default('');
            $table->unsignedInteger('reminder_count')->default(0);
            $table->timestamp('last_reminded_at')->nullable();
            $table->boolean('converted')->default(false);
            $table->unsignedBigInteger('converted_order_id')->default(0);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index(['converted', 'created_at'], 'nice_abandoned_carts_converted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_abandoned_carts');
    }
};
