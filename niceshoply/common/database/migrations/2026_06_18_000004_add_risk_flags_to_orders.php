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
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'risk_score')) {
                $table->unsignedTinyInteger('risk_score')->default(0)->after('admin_note')->comment('风险评分 0-100');
            }
            if (! Schema::hasColumn('orders', 'risk_flags')) {
                $table->json('risk_flags')->nullable()->after('risk_score')->comment('风险标记明细');
            }
            if (! Schema::hasColumn('orders', 'is_high_risk')) {
                $table->boolean('is_high_risk')->default(false)->after('risk_flags')->index('orders_is_high_risk_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach (['is_high_risk', 'risk_flags', 'risk_score'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
