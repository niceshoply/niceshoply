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
        if (! Schema::hasTable('search_keywords')) {
            Schema::create('search_keywords', function (Blueprint $table) {
                $table->id();
                $table->string('keyword', 100)->unique();
                $table->unsignedBigInteger('hits')->default(1);
                $table->unsignedBigInteger('results')->default(0);
                $table->timestamp('last_at')->nullable();
            });
        }

        if (! Schema::hasTable('search_synonyms')) {
            Schema::create('search_synonyms', function (Blueprint $table) {
                $table->id();
                // 同义词组，逗号分隔，如 "手机,智能手机,cellphone"
                $table->string('terms', 255);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('search_synonyms');
        Schema::dropIfExists('search_keywords');
    }
};
