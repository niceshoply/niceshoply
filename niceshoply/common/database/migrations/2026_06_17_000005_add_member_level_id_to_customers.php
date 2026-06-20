<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'member_level_id')) {
                $table->unsignedBigInteger('member_level_id')->default(0)->after('customer_group_id')
                    ->comment('会员等级ID，0=无');
                $table->index('member_level_id', 'customers_member_level_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'member_level_id')) {
                $table->dropIndex('customers_member_level_idx');
                $table->dropColumn('member_level_id');
            }
        });
    }
};
