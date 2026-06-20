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
        if (! Schema::hasTable('referral_codes')) {
            Schema::create('referral_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->string('code', 16)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('referral_bindings')) {
            Schema::create('referral_bindings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inviter_id')->index();
                $table->unsignedBigInteger('invitee_id')->unique();
                $table->string('code', 16);
                $table->timestamp('bound_at')->nullable();
            });
        }

        if (! Schema::hasTable('referral_rewards')) {
            Schema::create('referral_rewards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inviter_id')->index();
                $table->unsignedBigInteger('invitee_id')->default(0);
                $table->string('scene', 32); // register / first_order
                $table->string('reward_type', 16); // points / coupon
                $table->string('reward_value', 64)->default('');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referral_bindings');
        Schema::dropIfExists('referral_codes');
    }
};
