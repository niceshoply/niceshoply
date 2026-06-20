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
        if (Schema::hasTable('bxgy_rules')) {
            return;
        }

        Schema::create('bxgy_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('product_id')->default(0); // 0=全部商品(按单品分别计算)
            $table->unsignedInteger('buy_qty')->default(1);       // X
            $table->unsignedInteger('get_qty')->default(1);       // Y
            $table->unsignedTinyInteger('discount_percent')->default(50); // Y 件优惠百分比，100=免费
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bxgy_rules');
    }
};
