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
        if (! Schema::hasTable('risk_blacklist')) {
            Schema::create('risk_blacklist', function (Blueprint $table) {
                $table->id();
                $table->string('type', 16); // ip / email / phone
                $table->string('value', 191);
                $table->string('reason', 191)->nullable();
                $table->timestamps();
                $table->unique(['type', 'value'], 'uniq_risk_bl');
            });
        }

        if (! Schema::hasTable('risk_events')) {
            Schema::create('risk_events', function (Blueprint $table) {
                $table->id();
                $table->string('scene', 24)->index();   // register / order
                $table->string('level', 12)->default('low'); // low / medium / high
                $table->string('rule', 64)->nullable();
                $table->string('ip', 64)->nullable();
                $table->string('subject', 191)->nullable(); // email/phone/order no
                $table->text('detail')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_events');
        Schema::dropIfExists('risk_blacklist');
    }
};
