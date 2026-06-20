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
     * 执行迁移：创建促销活动表（促销引擎核心）。
     *
     * 该表承载「满减 / 折扣 / 阶梯 / 免运费」等自动促销规则，
     * 折扣在结账时经 feeList 以负费用项进入金额闭环。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_promotions')) {
            return;
        }

        Schema::create('nice_promotions', function (Blueprint $table) {
            $table->comment('促销活动');
            $table->id();
            $table->string('name')->comment('活动名称（内部标识）');
            // scope：cart=整单促销，product=指定商品促销
            $table->string('scope', 16)->default('cart')->comment('作用域 cart|product');
            // condition_type：none|min_amount|min_qty|tiered（阶梯）
            $table->string('condition_type', 32)->default('none')->comment('条件类型');
            $table->json('conditions')->nullable()->comment('条件参数（阈值/阶梯等）');
            // action_type：percent|fixed|free_shipping
            $table->string('action_type', 32)->default('fixed')->comment('优惠类型');
            $table->json('actions')->nullable()->comment('优惠参数（折扣值/上限/适用商品等）');
            $table->integer('priority')->default(0)->comment('优先级，越大越先应用');
            $table->boolean('exclusive')->default(false)->comment('是否互斥（命中后不再叠加其他活动）');
            $table->unsignedInteger('usage_limit')->default(0)->comment('总使用次数上限，0=不限');
            $table->unsignedInteger('used_count')->default(0)->comment('已使用次数');
            $table->unsignedInteger('per_customer_limit')->default(0)->comment('单客户次数上限，0=不限');
            $table->json('customer_group_ids')->nullable()->comment('限定客户分组，空=全部');
            $table->timestamp('starts_at')->nullable()->comment('开始时间');
            $table->timestamp('ends_at')->nullable()->comment('结束时间');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index(['active', 'starts_at', 'ends_at'], 'nice_promotions_active_window_idx');
            $table->index('priority', 'nice_promotions_priority_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_promotions');
    }
};
