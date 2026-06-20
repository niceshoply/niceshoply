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
        if (Schema::hasTable('freight_insurance_records')) {
            return;
        }

        Schema::create('freight_insurance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('order_number', 64)->nullable();
            $table->unsignedBigInteger('customer_id')->default(0);
            $table->decimal('premium', 12, 2)->default(0);
            $table->string('status', 16)->default('insured'); // insured / claimed / closed
            $table->timestamps();
            $table->unique(['order_id'], 'uniq_fi_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_insurance_records');
    }
};
