<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 为商品/分类/文章/页面翻译表增加 canonical 字段。
     */
    public function up(): void
    {
        foreach ([
            'product_translations',
            'category_translations',
            'article_translations',
            'page_translations',
        ] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'canonical')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('canonical', 1024)->nullable()->after('meta_keywords')->comment('规范链接');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'product_translations',
            'category_translations',
            'article_translations',
            'page_translations',
        ] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'canonical')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('canonical');
            });
        }
    }
};
