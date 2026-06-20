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
        if (! Schema::hasTable('lucky_prizes')) {
            Schema::create('lucky_prizes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('type', 16)->default('thanks'); // thanks / points / coupon
                $table->string('value', 64)->default('');       // points: 数量；coupon: coupon_id
                $table->unsignedInteger('weight')->default(1);   // 中奖权重
                $table->integer('stock')->default(-1);           // 库存，-1 不限
                $table->unsignedInteger('sort')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lucky_draws')) {
            Schema::create('lucky_draws', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('prize_id')->default(0);
                $table->string('prize_name', 100)->default('');
                $table->string('prize_type', 16)->default('thanks');
                $table->string('result_value', 64)->default(''); // 积分数 / 券码
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_draws');
        Schema::dropIfExists('lucky_prizes');
    }
};
