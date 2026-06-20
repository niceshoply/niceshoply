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
        if (Schema::hasTable('ewaybills')) {
            return;
        }

        Schema::create('ewaybills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('order_number', 64)->nullable();
            $table->string('shipper_code', 16);
            $table->string('logistic_code', 64)->nullable();
            $table->string('kdniao_order_code', 64)->nullable();
            $table->string('status', 16)->default('success'); // success / failed
            $table->text('message')->nullable();
            $table->json('raw')->nullable();
            $table->json('print_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ewaybills');
    }
};
