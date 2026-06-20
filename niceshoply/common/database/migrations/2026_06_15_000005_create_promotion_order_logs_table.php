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
     * 执行迁移：创建订单促销/优惠券应用流水表。
     *
     * 记录每笔订单命中的促销活动与优惠券快照，便于对账、退款回滚与统计分析。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_promotion_order_logs')) {
            return;
        }

        Schema::create('nice_promotion_order_logs', function (Blueprint $table) {
            $table->comment('订单促销应用流水');
            $table->id();
            $table->unsignedBigInteger('order_id')->index('nice_promo_log_order_idx')->comment('订单ID');
            $table->unsignedBigInteger('promotion_id')->nullable()->comment('促销活动ID（可空）');
            $table->unsignedBigInteger('coupon_id')->nullable()->comment('优惠券ID（可空）');
            $table->string('code', 64)->nullable()->comment('券码或活动标识');
            $table->decimal('discount_amount', 15, 4)->default(0)->comment('折扣金额（正数表示抵扣额）');
            $table->json('snapshot')->nullable()->comment('规则快照（用于回溯）');
            $table->timestamps();
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_promotion_order_logs');
    }
};
