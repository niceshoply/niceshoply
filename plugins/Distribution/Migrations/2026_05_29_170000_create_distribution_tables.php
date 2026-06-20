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
        if (! Schema::hasTable('distributors')) {
            Schema::create('distributors', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->string('code', 32)->unique();
                $table->unsignedBigInteger('parent_id')->default(0)->comment('上级推广员 customer_id')->index();
                $table->decimal('total_commission', 12, 2)->default(0);
                $table->decimal('settled_commission', 12, 2)->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('distribution_relations')) {
            Schema::create('distribution_relations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->unique()->comment('买家');
                $table->unsignedBigInteger('distributor_id')->index()->comment('推广员 customer_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('distribution_commissions')) {
            Schema::create('distribution_commissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('buyer_customer_id')->default(0);
                $table->unsignedBigInteger('distributor_customer_id')->index();
                $table->unsignedTinyInteger('level')->default(1);
                $table->decimal('base_amount', 12, 2)->default(0);
                $table->decimal('rate', 5, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('status', 16)->default('pending')->comment('pending/settled/cancelled');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_commissions');
        Schema::dropIfExists('distribution_relations');
        Schema::dropIfExists('distributors');
    }
};
