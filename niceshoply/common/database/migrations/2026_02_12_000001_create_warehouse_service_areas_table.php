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
        Schema::create('warehouse_service_areas', function (Blueprint $table) {
            $table->comment('Warehouse Service Area');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('warehouse_id')->index('wsa_warehouse_id')->comment('Warehouse ID');
            $table->unsignedInteger('country_id')->comment('Country ID');
            $table->unsignedInteger('state_id')->default(0)->comment('State ID, 0 = entire country');
            $table->timestamps();
            $table->unique(['warehouse_id', 'country_id', 'state_id'], 'wsa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_service_areas');
    }
};
