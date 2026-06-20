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
     * 执行迁移：创建优惠券核销记录表。
     *
     * 唯一约束 (coupon_id, order_id) 保证同一订单不会重复核销同一张券，
     * 是并发不超发的最后一道数据库防线。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_coupon_usages')) {
            return;
        }

        Schema::create('nice_coupon_usages', function (Blueprint $table) {
            $table->comment('优惠券核销记录');
            $table->id();
            $table->unsignedBigInteger('coupon_id')->comment('优惠券ID');
            $table->unsignedBigInteger('customer_id')->default(0)->comment('客户ID，0=游客');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->decimal('discount_amount', 15, 4)->default(0)->comment('实际抵扣金额');
            $table->timestamp('used_at')->nullable()->comment('核销时间');
            $table->timestamps();

            $table->unique(['coupon_id', 'order_id'], 'nice_coupon_usages_unique');
            $table->index('customer_id', 'nice_coupon_usages_customer_idx');
            $table->index('coupon_id', 'nice_coupon_usages_coupon_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_coupon_usages');
    }
};
