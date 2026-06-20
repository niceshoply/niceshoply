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
        if (! Schema::hasTable('cart_rules')) {
            Schema::create('cart_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191)->default('');
                $table->decimal('min_amount', 12, 2)->default(0)->comment('满额门槛');
                $table->string('discount_type', 16)->default('fixed')->comment('fixed 减额 / percent 折扣');
                $table->decimal('discount_value', 12, 2)->default(0);
                $table->decimal('max_discount', 12, 2)->default(0)->comment('最大优惠(0 不限，percent 有效)');
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_rules');
    }
};
