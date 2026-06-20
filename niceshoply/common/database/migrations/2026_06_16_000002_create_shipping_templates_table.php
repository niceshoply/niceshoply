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
     * 执行迁移：创建运费模板表。
     *
     * 模板归属某配送区域，按 calc_type（固定/按重量/按件数/按金额）+ rules 计费，
     * free_threshold 达额包邮。由 ShippingTemplateService 作为内置 quote provider 输出报价。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_shipping_templates')) {
            return;
        }

        Schema::create('nice_shipping_templates', function (Blueprint $table) {
            $table->comment('运费模板');
            $table->id();
            $table->string('name')->comment('模板名称（展示给顾客的配送方式名）');
            $table->unsignedBigInteger('zone_id')->nullable()->comment('配送区域ID，空=全区域');
            // calc_type：flat=固定，by_weight=按重量，by_qty=按件数，by_amount=按金额
            $table->string('calc_type', 16)->default('flat')->comment('计费方式');
            $table->json('rules')->nullable()->comment('计费规则（基础费/阶梯/单位费率等）');
            $table->decimal('free_threshold', 15, 4)->default(0)->comment('满额包邮门槛，0=不包邮');
            $table->integer('priority')->default(0)->comment('展示/匹配优先级');
            $table->boolean('active')->default(true)->comment('是否启用');
            $table->timestamps();

            $table->index('zone_id', 'nice_shipping_templates_zone_idx');
            $table->index(['active', 'priority'], 'nice_shipping_templates_active_priority_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_shipping_templates');
    }
};
