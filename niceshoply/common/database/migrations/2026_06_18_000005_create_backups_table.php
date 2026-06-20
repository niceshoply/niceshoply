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
        if (Schema::hasTable('nice_backups')) {
            return;
        }

        Schema::create('nice_backups', function (Blueprint $table) {
            $table->comment('系统数据备份记录');
            $table->id();
            $table->string('type', 16)->default('full')->comment('full|database|files');
            $table->string('status', 16)->default('pending')->comment('pending|running|completed|failed');
            $table->string('file_path', 512)->default('');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum', 64)->default('');
            $table->string('triggered_by', 16)->default('manual')->comment('manual|schedule');
            $table->unsignedBigInteger('admin_id')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'nice_backups_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_backups');
    }
};
