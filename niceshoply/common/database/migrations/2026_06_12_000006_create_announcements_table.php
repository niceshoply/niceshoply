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
     * 执行迁移：创建顶部公告表与其翻译表。
     */
    public function up(): void
    {
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->comment('顶部公告');
                $table->id();
                $table->string('plugin_code', 64)->nullable()->index()->comment('来源插件编码（可空）');
                $table->string('url')->nullable()->comment('跳转链接');
                $table->unsignedInteger('sort_order')->default(0)->index()->comment('排序');
                $table->boolean('active')->default(true)->index()->comment('是否启用');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcement_translations')) {
            Schema::create('announcement_translations', function (Blueprint $table) {
                $table->comment('公告翻译');
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->foreign('announcement_id', 'ann_trans_parent_fk')
                    ->references('id')->on('announcements')->cascadeOnDelete();
                $table->string('locale', 10)->index('ann_trans_locale_idx');
                $table->string('text')->comment('公告文本');
                $table->index('announcement_id', 'ann_trans_parent_idx');
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_translations');
        Schema::dropIfExists('announcements');
    }
};
