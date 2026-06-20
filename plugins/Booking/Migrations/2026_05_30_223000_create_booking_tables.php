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
        if (! Schema::hasTable('booking_services')) {
            Schema::create('booking_services', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->string('product_sku', 64)->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->unsignedInteger('duration_min')->default(60);
                $table->unsignedInteger('slot_interval_min')->default(60);
                $table->unsignedInteger('capacity')->default(1); // 单时段可约人数/单
                $table->string('open_time', 5)->default('09:00');
                $table->string('close_time', 5)->default('18:00');
                $table->string('open_weekdays', 32)->default('1,2,3,4,5,6,7'); // 1=周一..7=周日
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_id')->index();
                $table->unsignedBigInteger('customer_id')->default(0)->index();
                $table->string('customer_name', 64)->nullable();
                $table->string('phone', 32)->nullable();
                $table->date('booking_date')->index();
                $table->string('booking_time', 5); // HH:MM
                $table->unsignedInteger('people')->default(1);
                $table->string('status', 16)->default('pending'); // pending/confirmed/completed/cancelled
                $table->unsignedBigInteger('order_id')->nullable();
                $table->string('remark', 255)->nullable();
                $table->timestamps();
                $table->index(['service_id', 'booking_date', 'booking_time'], 'idx_service_slot');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_services');
    }
};
