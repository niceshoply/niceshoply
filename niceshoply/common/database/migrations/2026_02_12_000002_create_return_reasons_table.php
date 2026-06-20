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
        Schema::create('return_reasons', function (Blueprint $table) {
            $table->comment('Return Reasons');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 128)->comment('Reason Name');
            $table->string('description', 255)->default('')->comment('Description hint');
            $table->integer('sort_order')->default(0)->comment('Sort Order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_reasons');
    }
};
