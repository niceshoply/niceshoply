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
        if (! Schema::hasTable('currency_region_defaults')) {
            Schema::create('currency_region_defaults', function (Blueprint $table) {
                $table->id();
                $table->string('country_code', 8)->unique();
                $table->string('currency_code', 8);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_region_defaults');
    }
};
