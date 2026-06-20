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
    public function up(): void
    {
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique()->comment('券码');
                $table->string('name', 191)->default('')->comment('券名称');
                $table->string('type', 32)->default('fixed')->comment('类型：fixed 满减 / percent 折扣 / free_shipping 免运费');
                $table->decimal('value', 12, 2)->default(0)->comment('面值：fixed 为金额，percent 为折扣百分比');
                $table->decimal('min_amount', 12, 2)->default(0)->comment('使用门槛：最低订单金额');
                $table->decimal('max_discount', 12, 2)->default(0)->comment('最大优惠金额(0 不限，仅 percent 有效)');
                $table->unsignedInteger('usage_limit')->default(0)->comment('总可用次数(0 不限)');
                $table->unsignedInteger('used_count')->default(0)->comment('已使用次数');
                $table->unsignedInteger('per_customer_limit')->default(1)->comment('每人限用次数(0 不限)');
                $table->timestamp('start_at')->nullable()->comment('生效时间');
                $table->timestamp('end_at')->nullable()->comment('失效时间');
                $table->boolean('active')->default(true)->comment('是否启用');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->index();
                $table->string('code', 64)->index();
                $table->unsignedBigInteger('customer_id')->default(0)->index();
                $table->unsignedBigInteger('order_id')->default(0)->index();
                $table->decimal('discount', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
