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
        if (Schema::hasTable('site_notices')) {
            return;
        }

        Schema::create('site_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('type', 16)->default('popup')->comment('popup/bar');
            $table->text('content')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('scope', 16)->default('all')->comment('all/home');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_notices');
    }
};
