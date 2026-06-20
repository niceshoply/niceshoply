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
        if (Schema::hasTable('product_views')) {
            return;
        }

        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            // 访客标识：c:{customerId} 或 v:{visitorId}
            $table->string('visitor_key', 64)->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->timestamps();
            $table->unique(['visitor_key', 'product_id'], 'uniq_pv');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
