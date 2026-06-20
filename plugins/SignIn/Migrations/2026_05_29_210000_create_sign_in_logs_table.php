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
        if (Schema::hasTable('sign_in_logs')) {
            return;
        }

        Schema::create('sign_in_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->date('sign_date');
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('continuous_days')->default(1);
            $table->timestamps();

            $table->unique(['customer_id', 'sign_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sign_in_logs');
    }
};
