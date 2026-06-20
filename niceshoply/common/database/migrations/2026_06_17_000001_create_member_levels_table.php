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
        if (Schema::hasTable('nice_member_levels')) {
            return;
        }

        Schema::create('nice_member_levels', function (Blueprint $table) {
            $table->comment('会员等级');
            $table->id();
            $table->string('name')->comment('等级名称（内部标识）');
            $table->string('threshold_type', 16)->default('amount')->comment('升级门槛类型 amount|points');
            $table->decimal('threshold_value', 15, 4)->default(0)->comment('门槛值');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('会员折扣百分比 0-100');
            $table->boolean('free_shipping')->default(false)->comment('是否免运费');
            $table->integer('priority')->default(0)->comment('优先级，越大等级越高');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index(['active', 'priority'], 'nice_member_levels_active_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_member_levels');
    }
};
