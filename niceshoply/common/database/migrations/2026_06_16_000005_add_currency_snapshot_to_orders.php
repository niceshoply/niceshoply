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
     * 订单多币种快照 + 跨境形式发票字段。
     *
     * orders 表已有 currency_code / currency_value，本迁移补充：
     * - currency_snapshot_at：汇率锁定时间
     * - proforma_number：形式发票号
     * - customer_tax_id：客户税号（VAT/GST）
     * - destination_tax_amount：目的国额外税费
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'currency_snapshot_at')) {
                $table->timestamp('currency_snapshot_at')->nullable()->after('currency_value')
                    ->comment('汇率快照锁定时间');
            }
            if (! Schema::hasColumn('orders', 'proforma_number')) {
                $table->string('proforma_number', 64)->nullable()->after('admin_note')
                    ->comment('形式发票号（跨境）');
            }
            if (! Schema::hasColumn('orders', 'customer_tax_id')) {
                $table->string('customer_tax_id', 64)->nullable()->after('proforma_number')
                    ->comment('客户税号 VAT/GST');
            }
            if (! Schema::hasColumn('orders', 'destination_tax_amount')) {
                $table->decimal('destination_tax_amount', 15, 4)->default(0)->after('customer_tax_id')
                    ->comment('目的国额外税费');
            }
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['currency_snapshot_at', 'proforma_number', 'customer_tax_id', 'destination_tax_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
