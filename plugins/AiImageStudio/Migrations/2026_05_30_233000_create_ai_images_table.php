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
    public function up(): void
    {
        if (Schema::hasTable('ai_images')) {
            return;
        }

        Schema::create('ai_images', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->string('model', 64)->nullable();
            $table->string('size', 16)->nullable();
            $table->string('path', 255);          // storage 相对路径
            $table->unsignedBigInteger('operator_id')->default(0);
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_images');
    }
};
