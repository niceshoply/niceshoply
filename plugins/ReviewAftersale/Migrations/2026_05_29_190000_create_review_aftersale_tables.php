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
        if (! Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('order_id')->default(0);
                $table->unsignedTinyInteger('rating')->default(5);
                $table->text('content')->nullable();
                $table->json('images')->nullable();
                $table->string('status', 16)->default('pending')->comment('pending/approved/rejected');
                $table->string('reply', 500)->nullable()->comment('商家回复');
                $table->timestamps();

                $table->index(['product_id', 'status']);
            });
        }

        if (! Schema::hasTable('aftersale_requests')) {
            Schema::create('aftersale_requests', function (Blueprint $table) {
                $table->id();
                $table->string('number', 32)->unique();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('type', 16)->default('refund')->comment('refund/return/exchange');
                $table->string('reason', 191)->default('');
                $table->text('description')->nullable();
                $table->json('images')->nullable();
                $table->decimal('refund_amount', 12, 2)->default(0);
                $table->string('status', 16)->default('pending')->comment('pending/approved/rejected/processing/completed');
                $table->string('admin_remark', 500)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aftersale_requests');
        Schema::dropIfExists('product_reviews');
    }
};
