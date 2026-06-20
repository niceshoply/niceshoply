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
        if (! Schema::hasTable('bargain_activities')) {
            Schema::create('bargain_activities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 128);
                $table->unsignedBigInteger('sku_id')->index();
                $table->unsignedBigInteger('product_id')->default(0);
                $table->decimal('origin_price', 12, 2)->default(0)->comment('起始价(0=用SKU价)');
                $table->decimal('floor_price', 12, 2)->default(0)->comment('底价');
                $table->decimal('min_cut', 12, 2)->default(0.01)->comment('单次最小砍价');
                $table->decimal('max_cut', 12, 2)->default(1)->comment('单次最大砍价');
                $table->unsignedInteger('time_limit_minutes')->default(1440);
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bargain_tasks')) {
            Schema::create('bargain_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->decimal('origin_price', 12, 2)->default(0);
                $table->decimal('floor_price', 12, 2)->default(0);
                $table->decimal('current_price', 12, 2)->default(0);
                $table->string('status', 16)->default('cutting')->comment('cutting/done/expired/used');
                $table->unsignedBigInteger('order_id')->default(0);
                $table->dateTime('expire_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bargain_records')) {
            Schema::create('bargain_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedBigInteger('helper_customer_id')->default(0);
                $table->decimal('cut_amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bargain_records');
        Schema::dropIfExists('bargain_tasks');
        Schema::dropIfExists('bargain_activities');
    }
};
