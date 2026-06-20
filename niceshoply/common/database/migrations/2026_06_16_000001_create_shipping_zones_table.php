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
     * 执行迁移：创建配送区域表。
     *
     * 区域以国家/省州集合界定，按 priority 匹配（越大越先命中），用于内置运费模板分区计费。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_shipping_zones')) {
            return;
        }

        Schema::create('nice_shipping_zones', function (Blueprint $table) {
            $table->comment('配送区域');
            $table->id();
            $table->string('name')->comment('区域名称');
            $table->json('country_ids')->nullable()->comment('适用国家ID集合，空=全部国家');
            $table->json('state_ids')->nullable()->comment('适用省/州ID集合，空=区域内全部');
            $table->integer('priority')->default(0)->comment('匹配优先级，越大越先命中');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index(['active', 'priority'], 'nice_shipping_zones_active_priority_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_shipping_zones');
    }
};
