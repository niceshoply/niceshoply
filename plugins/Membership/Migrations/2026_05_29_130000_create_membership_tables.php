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
        if (! Schema::hasTable('membership_levels')) {
            Schema::create('membership_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name', 64);
                $table->decimal('min_spent', 12, 2)->default(0)->comment('升级所需累计消费');
                $table->decimal('discount_percent', 5, 2)->default(0)->comment('会员折扣百分比(0-100，表示优惠多少%)');
                $table->unsignedInteger('sort')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('customer_memberships')) {
            Schema::create('customer_memberships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->unsignedBigInteger('level_id')->default(0)->index();
                $table->decimal('total_spent', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_memberships');
        Schema::dropIfExists('membership_levels');
    }
};
