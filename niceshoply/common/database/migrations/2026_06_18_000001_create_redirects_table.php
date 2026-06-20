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
        if (Schema::hasTable('nice_redirects')) {
            return;
        }

        Schema::create('nice_redirects', function (Blueprint $table) {
            $table->comment('URL 重定向规则');
            $table->id();
            $table->string('source_path', 512)->unique('nice_redirects_source_unique')->comment('来源路径，如 /old-url');
            $table->string('target_path', 1024)->comment('目标路径或完整 URL');
            $table->unsignedSmallInteger('status_code')->default(301)->comment('301 或 302');
            $table->unsignedBigInteger('hits')->default(0)->comment('命中次数');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'source_path'], 'nice_redirects_active_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_redirects');
    }
};
