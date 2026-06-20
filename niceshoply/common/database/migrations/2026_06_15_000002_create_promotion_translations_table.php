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
     * 执行迁移：创建促销活动多语言文案表。
     *
     * label/description 为面向顾客展示的多语言文案（如「全场满 200 减 30」）。
     */
    public function up(): void
    {
        if (Schema::hasTable('nice_promotion_translations')) {
            return;
        }

        Schema::create('nice_promotion_translations', function (Blueprint $table) {
            $table->comment('促销活动翻译');
            $table->id();
            $table->unsignedBigInteger('promotion_id');
            $table->foreign('promotion_id', 'nice_promo_trans_parent_fk')
                ->references('id')->on('nice_promotions')->cascadeOnDelete();
            $table->string('locale', 10)->index('nice_promo_trans_locale_idx');
            $table->string('label')->comment('展示标题');
            $table->string('description')->nullable()->comment('展示描述');
            $table->index('promotion_id', 'nice_promo_trans_parent_idx');
        });
    }

    /**
     * 回滚迁移。
     */
    public function down(): void
    {
        Schema::dropIfExists('nice_promotion_translations');
    }
};
