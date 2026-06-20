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
        if (Schema::hasTable('nice_point_logs')) {
            return;
        }

        Schema::create('nice_point_logs', function (Blueprint $table) {
            $table->comment('积分流水');
            $table->id();
            $table->unsignedBigInteger('customer_id')->index('nice_point_logs_customer_idx');
            $table->string('type', 16)->comment('earn|spend|expire|adjust');
            $table->integer('points')->comment('变动积分（正=增加，负=减少）');
            $table->string('source', 32)->default('')->comment('来源标识 order/refund/admin 等');
            $table->unsignedBigInteger('reference_id')->default(0)->comment('关联ID（订单等）');
            $table->timestamp('expires_at')->nullable()->comment('过期时间（earn 类型）');
            $table->string('comment')->nullable()->comment('备注');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_point_logs');
    }
};
