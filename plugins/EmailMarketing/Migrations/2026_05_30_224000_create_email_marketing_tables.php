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
        if (! Schema::hasTable('email_subscribers')) {
            Schema::create('email_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email', 191)->unique();
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->boolean('subscribed')->default(true);
                $table->string('token', 40)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('email_campaigns')) {
            Schema::create('email_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('subject', 255);
                $table->longText('body');
                $table->string('target', 20)->default('subscribers'); // subscribers / customers
                $table->string('status', 16)->default('draft'); // draft / sending / sent
                $table->dateTime('scheduled_at')->nullable();
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('fail_count')->default(0);
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_subscribers');
    }
};
