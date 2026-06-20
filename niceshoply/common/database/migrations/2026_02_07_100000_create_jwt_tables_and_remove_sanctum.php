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

/**
 * 创建 JWT Token 管理表，并移除 Sanctum 的 personal_access_tokens 表。
 *
 * jwt_tokens: 记录已签发的 token，支持多设备管理
 * jwt_blacklist: Token 黑名单，用于注销后使 token 失效
 */
return new class extends Migration
{
    public function up(): void
    {
        // JWT Token 记录表 - 支持多设备
        Schema::create('jwt_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_id', 64)->unique()->comment('JWT jti claim');
            $table->morphs('tokenable'); // tokenable_type + tokenable_id (Admin/Customer)
            $table->string('guard', 32)->comment('Auth guard: admin_api / customer_api');
            $table->string('device_name', 128)->nullable()->comment('设备名称');
            $table->timestamp('expires_at')->nullable()->comment('Token 过期时间');
            $table->timestamp('last_used_at')->nullable()->comment('最后使用时间');
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id'], 'jwt_tokens_tokenable_index');
        });

        // JWT 黑名单表
        Schema::create('jwt_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('token_id', 64)->unique()->comment('JWT jti claim');
            $table->timestamp('expired_at')->comment('原 Token 过期时间');
            $table->timestamp('created_at')->useCurrent()->comment('加入黑名单时间');

            $table->index('expired_at', 'jwt_blacklist_expired_index');
        });

        // 移除 Sanctum 的 personal_access_tokens 表
        Schema::dropIfExists('personal_access_tokens');
    }

    public function down(): void
    {
        Schema::dropIfExists('jwt_tokens');
        Schema::dropIfExists('jwt_blacklist');

        // 恢复 Sanctum 的 personal_access_tokens 表
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
};
