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
        if (! Schema::hasTable('group_buy_activities')) {
            Schema::create('group_buy_activities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 128);
                $table->unsignedBigInteger('sku_id')->index();
                $table->unsignedBigInteger('product_id')->default(0);
                $table->decimal('group_price', 12, 2)->default(0);
                $table->unsignedInteger('group_size')->default(2)->comment('成团人数');
                $table->unsignedInteger('time_limit_minutes')->default(1440)->comment('开团到成团时限(分钟)');
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('group_buy_groups')) {
            Schema::create('group_buy_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id')->index();
                $table->unsignedBigInteger('leader_customer_id')->default(0);
                $table->string('status', 16)->default('open')->comment('open/success/failed');
                $table->unsignedInteger('members_count')->default(0);
                $table->dateTime('expire_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('group_buy_members')) {
            Schema::create('group_buy_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('order_id')->default(0);
                $table->boolean('is_leader')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_buy_members');
        Schema::dropIfExists('group_buy_groups');
        Schema::dropIfExists('group_buy_activities');
    }
};
