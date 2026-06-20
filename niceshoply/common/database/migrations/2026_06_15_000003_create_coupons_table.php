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
     * 执行迁移：创建优惠券表。
     *
     * 优惠券既可独立配置折扣（type/value），也可关联促销活动（promotion_id）复用其规则。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_coupons')) {
            return;
        }

        Schema::create('nice_coupons', function (Blueprint $table) {
            $table->comment('优惠券');
            $table->id();
            $table->string('code', 64)->unique()->comment('券码（唯一）');
            $table->unsignedBigInteger('promotion_id')->nullable()->comment('关联促销活动（可空）');
            // type：percent=百分比，fixed=固定金额，free_shipping=免运费
            $table->string('type', 16)->default('fixed')->comment('折扣类型 percent|fixed|free_shipping');
            $table->decimal('value', 15, 4)->default(0)->comment('折扣值（百分比 0-100 或固定金额）');
            $table->decimal('min_amount', 15, 4)->default(0)->comment('使用门槛金额，0=无门槛');
            $table->unsignedInteger('total_limit')->default(0)->comment('总发放/可用次数上限，0=不限');
            $table->unsignedInteger('used_count')->default(0)->comment('已使用次数');
            $table->unsignedInteger('per_customer_limit')->default(1)->comment('单客户可用次数，0=不限');
            $table->timestamp('starts_at')->nullable()->comment('生效时间');
            $table->timestamp('ends_at')->nullable()->comment('失效时间');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index(['active', 'starts_at', 'ends_at'], 'nice_coupons_active_window_idx');
            $table->index('promotion_id', 'nice_coupons_promotion_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_coupons');
    }
};
