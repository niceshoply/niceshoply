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
        if (Schema::hasTable('customer_device_tokens')) {
            return;
        }

        Schema::create('customer_device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('token', 512);
            $table->string('platform', 32)->default('');
            $table->timestamps();

            $table->index('customer_id');
            $table->unique(['customer_id', 'token'], 'customer_device_tokens_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_device_tokens');
    }
};
