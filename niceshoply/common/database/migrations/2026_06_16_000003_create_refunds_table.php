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
     * 执行迁移：创建退款单表。
     *
     * 退款单是退款全流程的载体（区别于 order_return_payments 的即时记账），
     * 通过状态机 pending→processing→succeeded/failed 推进，支持原路退回、
     * 退回钱包余额、人工线下退款三种方式。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_refunds')) {
            return;
        }

        Schema::create('nice_refunds', function (Blueprint $table) {
            $table->comment('退款单');
            $table->id();
            $table->string('number', 32)->unique()->comment('退款单号');
            $table->unsignedBigInteger('order_id')->comment('订单ID');
            $table->unsignedBigInteger('order_return_id')->nullable()->comment('关联退货单ID（可空）');
            $table->unsignedBigInteger('customer_id')->default(0)->comment('客户ID，0=游客');
            $table->decimal('amount', 15, 4)->default(0)->comment('退款金额');
            $table->string('currency_code', 3)->default('')->comment('币种');
            $table->decimal('currency_value', 15, 8)->default(1)->comment('下单汇率快照');
            // method：original=原路退回，balance=退回钱包余额，manual=人工线下退款
            $table->string('method', 16)->default('original')->comment('退款方式 original|balance|manual');
            // status：pending=待处理，processing=处理中，succeeded=成功，failed=失败，cancelled=已取消
            $table->string('status', 16)->default('pending')->comment('状态机状态');
            $table->string('gateway', 32)->nullable()->comment('支付网关标识（原路退回时）');
            $table->string('gateway_ref', 128)->nullable()->comment('网关退款流水号');
            $table->string('reason')->nullable()->comment('退款原因');
            $table->unsignedBigInteger('operator_id')->default(0)->comment('操作人（后台用户ID），0=系统');
            $table->timestamp('processed_at')->nullable()->comment('完成时间');
            $table->timestamps();

            $table->index('order_id', 'nice_refunds_order_idx');
            $table->index('order_return_id', 'nice_refunds_return_idx');
            $table->index('customer_id', 'nice_refunds_customer_idx');
            $table->index('status', 'nice_refunds_status_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_refunds');
    }
};
