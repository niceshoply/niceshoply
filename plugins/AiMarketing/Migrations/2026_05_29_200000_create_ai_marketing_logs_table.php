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
        if (Schema::hasTable('ai_marketing_logs')) {
            return;
        }

        Schema::create('ai_marketing_logs', function (Blueprint $table) {
            $table->id();
            $table->string('scene', 32)->index()->comment('product_title/product_desc/selling_point/seo_meta/sms/email/social');
            $table->string('provider', 32)->default('');
            $table->text('input')->nullable();
            $table->longText('output')->nullable();
            $table->unsignedBigInteger('operator_id')->default(0)->comment('后台操作员ID');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_marketing_logs');
    }
};
