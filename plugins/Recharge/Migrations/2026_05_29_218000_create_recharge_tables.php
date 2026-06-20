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
        if (! Schema::hasTable('recharge_plans')) {
            Schema::create('recharge_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name', 128);
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recharge_orders')) {
            Schema::create('recharge_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->unique();
                $table->unsignedBigInteger('customer_id')->index();
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->string('status', 16)->default('pending')->comment('pending/credited');
                $table->timestamp('credited_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recharge_orders');
        Schema::dropIfExists('recharge_plans');
    }
};
