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
        if (! Schema::hasTable('gift_card_batches')) {
            Schema::create('gift_card_batches', function (Blueprint $table) {
                $table->id();
                $table->string('name', 128);
                $table->decimal('face_value', 12, 2)->default(0);
                $table->unsignedInteger('quantity')->default(0);
                $table->string('prefix', 16)->nullable();
                $table->date('expire_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('gift_cards')) {
            Schema::create('gift_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id')->index();
                $table->string('code', 32)->unique();
                $table->string('pin', 32);
                $table->decimal('face_value', 12, 2)->default(0);
                $table->decimal('balance', 12, 2)->default(0);
                $table->string('status', 16)->default('unused')->comment('unused/used/disabled');
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->timestamp('redeemed_at')->nullable();
                $table->date('expire_at')->nullable();
                $table->timestamps();

                $table->index(['status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('gift_card_batches');
    }
};
