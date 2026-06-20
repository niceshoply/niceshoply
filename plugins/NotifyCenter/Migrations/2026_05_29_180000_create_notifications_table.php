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
        if (! Schema::hasTable('member_notifications')) {
            Schema::create('member_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index()->comment('0=全员广播');
                $table->string('title', 191);
                $table->text('content')->nullable();
                $table->string('type', 32)->default('system')->comment('system/order/promotion/...');
                $table->unsignedBigInteger('ref_id')->default(0)->comment('关联订单等ID');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_notifications');
    }
};
