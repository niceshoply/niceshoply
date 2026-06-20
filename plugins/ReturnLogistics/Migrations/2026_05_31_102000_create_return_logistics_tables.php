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
        if (! Schema::hasTable('return_addresses')) {
            Schema::create('return_addresses', function (Blueprint $table) {
                $table->id();
                $table->string('name', 64);
                $table->string('contact', 64)->nullable();
                $table->string('phone', 32)->nullable();
                $table->string('province', 64)->nullable();
                $table->string('city', 64)->nullable();
                $table->string('area', 64)->nullable();
                $table->string('address', 255);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('return_shipments')) {
            Schema::create('return_shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('aftersale_id')->default(0)->index();
                $table->unsignedBigInteger('order_id')->default(0)->index();
                $table->string('order_number', 64)->nullable();
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->unsignedBigInteger('return_address_id')->default(0);
                $table->string('shipper_code', 16)->nullable();
                $table->string('tracking_no', 64)->nullable()->index();
                $table->string('status', 16)->default('pending'); // pending/in_transit/received
                $table->text('remark')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('return_shipments');
        Schema::dropIfExists('return_addresses');
    }
};
