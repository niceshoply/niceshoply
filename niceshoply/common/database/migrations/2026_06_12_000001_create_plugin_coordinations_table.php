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
     * 执行迁移。
     */
    public function up(): void
    {
        if (! Schema::hasTable('plugin_coordinations')) {
            Schema::create('plugin_coordinations', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50)->comment('插件类型：price、orderfee 等');
                $table->json('sort_order')->nullable()->comment('插件执行顺序');
                $table->string('exclusive_mode', 20)->default('all_stack')->comment('互斥模式：first_only、all_stack、custom');
                $table->json('exclusive_pairs')->nullable()->comment('自定义互斥插件对');
                $table->timestamps();

                $table->unique('type');
            });
        }
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_coordinations');
    }
};
