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
        if (! Schema::hasTable('point_accounts')) {
            Schema::create('point_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->integer('balance')->default(0)->comment('积分余额');
                $table->integer('total_earned')->default(0);
                $table->integer('total_spent')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('point_logs')) {
            Schema::create('point_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->integer('change')->comment('正数获取，负数消耗');
                $table->integer('balance_after')->default(0);
                $table->string('type', 32)->default('order')->comment('order/redeem/adjust/...');
                $table->unsignedBigInteger('order_id')->default(0)->index();
                $table->string('remark', 191)->default('');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('point_logs');
        Schema::dropIfExists('point_accounts');
    }
};
