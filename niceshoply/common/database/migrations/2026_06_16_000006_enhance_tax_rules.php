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
     * 扩展税务规则：VAT/GST 方案、跨境目的国、税号校验。
     */
    public function up(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_rates', 'scheme')) {
                $table->string('scheme', 16)->default('vat')->after('type')
                    ->comment('税制方案 vat|gst|sales');
            }
            if (! Schema::hasColumn('tax_rates', 'requires_tax_id')) {
                $table->boolean('requires_tax_id')->default(false)->after('rate')
                    ->comment('是否要求客户提供税号');
            }
        });

        Schema::table('tax_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_rules', 'cross_border')) {
                $table->boolean('cross_border')->default(false)->after('priority')
                    ->comment('是否适用于跨境（目的国≠店铺国）');
            }
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::table('tax_rates', function (Blueprint $table) {
            foreach (['scheme', 'requires_tax_id'] as $col) {
                if (Schema::hasColumn('tax_rates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('tax_rules', function (Blueprint $table) {
            if (Schema::hasColumn('tax_rules', 'cross_border')) {
                $table->dropColumn('cross_border');
            }
        });
    }
};
