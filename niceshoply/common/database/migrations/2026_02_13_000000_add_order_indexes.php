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

/**
 * orders.number 与 orders.status 是支付回调、订单查询、后台筛选的高频条件，
 * 初始建表未建索引，补充索引以避免大表全表扫描。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'number')) {
                $table->index('number', 'o_number');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->index('status', 'o_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('o_number');
            $table->dropIndex('o_status');
        });
    }
};
