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
     * 执行迁移：创建 Newsletter 订阅者表。
     */
    public function up(): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->comment('Newsletter 订阅者');
                $table->bigIncrements('id')->comment('ID');
                $table->string('email')->unique()->comment('邮箱地址');
                $table->string('name')->nullable()->comment('订阅者姓名');
                $table->unsignedInteger('customer_id')->nullable()->index()->comment('客户 ID（已注册用户）');
                $table->string('status')->default('active')->comment('状态：active/unsubscribed/bounced');
                $table->string('source')->nullable()->comment('订阅来源：footer/popup/checkout 等');
                $table->timestamp('subscribed_at')->nullable()->comment('订阅时间');
                $table->timestamp('unsubscribed_at')->nullable()->comment('退订时间');
                $table->text('notes')->nullable()->comment('管理员备注');
                $table->timestamps();

                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
