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
        if (Schema::hasTable('wholesale_tiers')) {
            return;
        }

        Schema::create('wholesale_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sku_id')->index();
            $table->string('product_sku', 64)->nullable();
            $table->unsignedInteger('min_qty')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['sku_id', 'min_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_tiers');
    }
};
