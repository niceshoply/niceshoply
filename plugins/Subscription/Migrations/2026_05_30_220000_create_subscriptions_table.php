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
        if (Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('product_id')->default(0);
            $table->string('product_sku', 64)->index();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('interval_unit', 10)->default('month'); // day / week / month
            $table->unsignedInteger('interval_count')->default(1);
            $table->dateTime('next_run_at')->nullable()->index();
            $table->unsignedBigInteger('shipping_address_id')->default(0);
            $table->unsignedBigInteger('billing_address_id')->default(0);
            $table->string('payment_mode', 20)->default('reminder'); // reminder / auto_balance
            $table->string('status', 20)->default('active')->index();  // active / paused / cancelled
            $table->unsignedInteger('cycles_done')->default(0);
            $table->unsignedBigInteger('last_order_id')->nullable();
            $table->dateTime('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
