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
        if (Schema::hasTable('nice_customer_points')) {
            return;
        }

        Schema::create('nice_customer_points', function (Blueprint $table) {
            $table->comment('客户积分账户');
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique('nice_customer_points_customer_unique');
            $table->unsignedInteger('balance')->default(0)->comment('可用积分');
            $table->unsignedInteger('total_earned')->default(0)->comment('累计获得');
            $table->unsignedInteger('total_spent')->default(0)->comment('累计消费');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_customer_points');
    }
};
