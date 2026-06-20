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
        if (Schema::hasTable('nice_schedule_runs')) {
            return;
        }

        Schema::create('nice_schedule_runs', function (Blueprint $table) {
            $table->comment('计划任务执行记录');
            $table->id();
            $table->string('command', 128)->index('nice_schedule_runs_command_idx');
            $table->string('expression', 64)->default('');
            $table->string('status', 16)->default('success')->comment('success|failed|manual');
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('ran_at');
            $table->timestamps();

            $table->index(['command', 'ran_at'], 'nice_schedule_runs_command_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_schedule_runs');
    }
};
