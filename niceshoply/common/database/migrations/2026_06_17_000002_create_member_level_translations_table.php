<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nice_member_level_translations')) {
            return;
        }

        Schema::create('nice_member_level_translations', function (Blueprint $table) {
            $table->comment('会员等级翻译');
            $table->id();
            $table->unsignedBigInteger('member_level_id');
            $table->foreign('member_level_id', 'nice_ml_trans_parent_fk')
                ->references('id')->on('nice_member_levels')->cascadeOnDelete();
            $table->string('locale', 10)->index('nice_ml_trans_locale_idx');
            $table->string('label')->comment('展示名称');
            $table->string('description')->nullable()->comment('展示描述');
            $table->index('member_level_id', 'nice_ml_trans_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_member_level_translations');
    }
};
