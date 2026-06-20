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
        if (! Schema::hasTable('sms_campaigns')) {
            Schema::create('sms_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('template_id', 64);
                $table->json('template_data')->nullable();
                $table->string('target', 20)->default('customers');
                $table->string('status', 16)->default('draft');
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('fail_count')->default(0);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sms_unsubscribes')) {
            Schema::create('sms_unsubscribes', function (Blueprint $table) {
                $table->id();
                $table->string('mobile', 20)->unique();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_unsubscribes');
        Schema::dropIfExists('sms_campaigns');
    }
};
