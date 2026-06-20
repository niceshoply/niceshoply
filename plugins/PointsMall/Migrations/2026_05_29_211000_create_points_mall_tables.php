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
        if (! Schema::hasTable('points_mall_items')) {
            Schema::create('points_mall_items', function (Blueprint $table) {
                $table->id();
                $table->string('title', 191);
                $table->string('image', 500)->nullable();
                $table->string('type', 16)->default('goods')->comment('goods/coupon');
                $table->unsignedBigInteger('ref_id')->default(0)->comment('关联商品/券ID');
                $table->unsignedInteger('points_cost')->default(0);
                $table->decimal('cash_cost', 12, 2)->default(0)->comment('混合兑换需付现金');
                $table->integer('stock')->default(0);
                $table->unsignedInteger('per_limit')->default(0)->comment('每人限兑，0不限');
                $table->unsignedInteger('redeemed_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('points_mall_redemptions')) {
            Schema::create('points_mall_redemptions', function (Blueprint $table) {
                $table->id();
                $table->string('number', 32)->unique();
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('title', 191);
                $table->unsignedInteger('points_cost')->default(0);
                $table->decimal('cash_cost', 12, 2)->default(0);
                $table->unsignedInteger('quantity')->default(1);
                $table->string('status', 16)->default('pending')->comment('pending/shipped/completed/cancelled');
                $table->string('contact', 191)->nullable()->comment('收货联系方式/地址');
                $table->string('remark', 500)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('points_mall_redemptions');
        Schema::dropIfExists('points_mall_items');
    }
};
