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
     * 执行迁移：为商品表新增「最低起订量」字段。
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'minimum')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('minimum')->default(1)->after('viewed')->comment('最低起订量');
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'minimum')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('minimum');
            });
        }
    }
};
