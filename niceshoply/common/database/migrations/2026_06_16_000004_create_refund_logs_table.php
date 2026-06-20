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
    /**
     * 执行迁移：创建退款单流水表。
     *
     * 记录退款单每一次状态变更与网关交互，用于审计与排障。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_refund_logs')) {
            return;
        }

        Schema::create('nice_refund_logs', function (Blueprint $table) {
            $table->comment('退款单流水');
            $table->id();
            $table->unsignedBigInteger('refund_id')->index('nice_refund_logs_refund_idx')->comment('退款单ID');
            $table->string('from_status', 16)->nullable()->comment('原状态');
            $table->string('to_status', 16)->comment('目标状态');
            $table->string('comment')->nullable()->comment('备注');
            $table->json('context')->nullable()->comment('上下文快照（网关返回等）');
            $table->unsignedBigInteger('operator_id')->default(0)->comment('操作人，0=系统');
            $table->timestamps();
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_refund_logs');
    }
};
