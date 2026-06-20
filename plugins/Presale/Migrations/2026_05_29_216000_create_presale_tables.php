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
        if (! Schema::hasTable('presale_activities')) {
            Schema::create('presale_activities', function (Blueprint $table) {
                $table->id();
                $table->string('name', 128);
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->date('ship_date')->nullable()->comment('预计发货日期');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('presale_items')) {
            Schema::create('presale_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('presale_id')->index();
                $table->unsignedBigInteger('sku_id')->index();
                $table->unsignedBigInteger('product_id')->default(0);
                $table->decimal('presale_price', 12, 2)->default(0);
                $table->decimal('deposit', 12, 2)->default(0)->comment('定金');
                $table->decimal('expand', 12, 2)->default(0)->comment('定金膨胀可抵金额');
                $table->integer('qty_limit')->default(0)->comment('限量，0不限');
                $table->unsignedInteger('sold')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_items');
        Schema::dropIfExists('presale_activities');
    }
};
