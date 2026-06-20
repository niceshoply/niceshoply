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
        if (! Schema::hasTable('marketing_flows')) {
            Schema::create('marketing_flows', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('trigger_event', 32)->index(); // register / order_placed / order_paid
                $table->unsignedInteger('delay_minutes')->default(0);
                $table->string('title', 191);
                $table->text('content')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('sent_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('marketing_flow_jobs')) {
            Schema::create('marketing_flow_jobs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flow_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('title', 191);
                $table->text('content')->nullable();
                $table->timestamp('run_at')->index();
                $table->string('status', 12)->default('pending'); // pending / sent / failed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_flow_jobs');
        Schema::dropIfExists('marketing_flows');
    }
};
