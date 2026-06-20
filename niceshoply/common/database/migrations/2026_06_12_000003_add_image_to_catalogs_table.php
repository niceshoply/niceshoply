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
     * 执行迁移：为商品目录表新增封面图字段。
     */
    public function up(): void
    {
        if (Schema::hasTable('catalogs') && ! Schema::hasColumn('catalogs', 'image')) {
            Schema::table('catalogs', function (Blueprint $table) {
                $table->string('image')->nullable()->after('slug')->comment('目录封面图');
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        if (Schema::hasTable('catalogs') && Schema::hasColumn('catalogs', 'image')) {
            Schema::table('catalogs', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
