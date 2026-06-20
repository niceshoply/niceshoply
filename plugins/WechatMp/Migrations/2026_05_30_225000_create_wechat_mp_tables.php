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
        if (! Schema::hasTable('wechat_users')) {
            Schema::create('wechat_users', function (Blueprint $table) {
                $table->id();
                $table->string('openid', 64)->index();
                $table->string('unionid', 64)->nullable()->index();
                $table->string('source', 16)->default('mini'); // mini / oa
                $table->unsignedBigInteger('customer_id')->default(0)->index();
                $table->timestamps();
                $table->unique(['openid', 'source'], 'uniq_openid_source');
            });
        }

        if (! Schema::hasTable('wechat_auto_replies')) {
            Schema::create('wechat_auto_replies', function (Blueprint $table) {
                $table->id();
                $table->string('match_type', 12)->default('equal'); // equal / contains / default
                $table->string('keyword', 128)->nullable();
                $table->text('content');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wechat_auto_replies');
        Schema::dropIfExists('wechat_users');
    }
};
