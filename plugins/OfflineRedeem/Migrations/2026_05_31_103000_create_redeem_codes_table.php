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
        if (Schema::hasTable('redeem_codes')) {
            return;
        }

        Schema::create('redeem_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('type', 32)->default('voucher'); // voucher/booking/gift
            $table->unsignedBigInteger('ref_id')->default(0);
            $table->unsignedBigInteger('customer_id')->default(0);
            $table->string('title', 191)->default('');
            $table->string('status', 16)->default('active'); // active/redeemed/expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->string('redeemed_by', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redeem_codes');
    }
};
