<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class JwtTokenService
{
    /**
     * 获取 JWT guard 实例。
     */
    private function guard(string $guard): JWTGuard
    {
        /** @var JWTGuard $instance */
        $instance = auth()->guard($guard);

        return $instance;
    }

    /**
     * 签发 Token（access_token），记录到 jwt_tokens 表。
     *
     * @param  JWTSubject&Model  $user
     * @param  string  $guard  API guard 名称 (admin_api / customer_api)
     * @param  string|null  $deviceName  设备名称
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function issueToken(JWTSubject $user, string $guard, ?string $deviceName = null): array
    {
        $jwtGuard = $this->guard($guard);

        // 使用指定 guard 签发 JWT
        $token = $jwtGuard->login($user);

        // 解析 payload 获取 jti 和过期时间
        $payload   = $jwtGuard->payload();
        $tokenId   = $payload->get('jti');
        $expiresAt = Carbon::createFromTimestamp($payload->get('exp'));
        $ttl       = config('jwt.ttl', 60);

        // 记录 token 到数据库
        DB::table('jwt_tokens')->insert([
            'token_id'       => $tokenId,
            'tokenable_type' => get_class($user),
            'tokenable_id'   => $user->getKey(),
            'guard'          => $guard,
            'device_name'    => $deviceName,
            'expires_at'     => $expiresAt,
            'last_used_at'   => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttl * 60, // 秒
        ];
    }

    /**
     * 刷新 Token: 使旧 token 失效，签发新 token。
     *
     * @param  string  $guard  API guard 名称
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function refreshToken(string $guard): array
    {
        $jwtGuard = $this->guard($guard);

        // 获取旧 token 的 jti 和设备名
        $oldPayload = $jwtGuard->payload();
        $oldTokenId = $oldPayload->get('jti');
        $deviceName = DB::table('jwt_tokens')
            ->where('token_id', $oldTokenId)
            ->value('device_name');

        // 删除旧 token 记录
        DB::table('jwt_tokens')->where('token_id', $oldTokenId)->delete();

        // 刷新 token (jwt-auth 会自动将旧 token 加入黑名单)
        $newToken = $jwtGuard->refresh();
        $ttl      = config('jwt.ttl', 60);

        // 解析新 token 的信息
        $jwtGuard->setToken($newToken);
        $newPayload = $jwtGuard->payload();
        $newTokenId = $newPayload->get('jti');
        $expiresAt  = Carbon::createFromTimestamp($newPayload->get('exp'));
        $user       = $jwtGuard->user();

        // 记录新 token
        if ($user) {
            DB::table('jwt_tokens')->insert([
                'token_id'       => $newTokenId,
                'tokenable_type' => get_class($user),
                'tokenable_id'   => $user->getKey(),
                'guard'          => $guard,
                'device_name'    => $deviceName,
                'expires_at'     => $expiresAt,
                'last_used_at'   => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return [
            'access_token' => $newToken,
            'token_type'   => 'bearer',
            'expires_in'   => $ttl * 60,
        ];
    }

    /**
     * 注销当前 Token。
     *
     * @param  string  $guard  API guard 名称
     * @return void
     */
    public function revokeCurrentToken(string $guard): void
    {
        try {
            $jwtGuard = $this->guard($guard);
            $payload  = $jwtGuard->payload();
            $tokenId  = $payload->get('jti');

            // 从 jwt_tokens 表删除记录
            DB::table('jwt_tokens')->where('token_id', $tokenId)->delete();

            // jwt-auth 将 token 加入黑名单
            $jwtGuard->invalidate();
        } catch (\Exception $e) {
            // Token 可能已经无效，静默处理
        }
    }

    /**
     * 注销用户所有 Token（所有设备）。
     *
     * @param  Model  $user
     * @return void
     */
    public function revokeAllTokens(Model $user): void
    {
        // 删除所有 token 记录
        DB::table('jwt_tokens')
            ->where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->getKey())
            ->delete();
    }

    /**
     * 获取用户所有活跃 Token（多设备管理）。
     *
     * @param  Model  $user
     * @return \Illuminate\Support\Collection
     */
    public function getActiveTokens(Model $user): \Illuminate\Support\Collection
    {
        return DB::table('jwt_tokens')
            ->where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->getKey())
            ->where('expires_at', '>', now())
            ->get(['id', 'device_name', 'last_used_at', 'created_at', 'expires_at']);
    }

    /**
     * 更新 Token 最后使用时间。
     *
     * @param  string  $tokenId  JWT jti claim
     * @return void
     */
    public function touchToken(string $tokenId): void
    {
        DB::table('jwt_tokens')
            ->where('token_id', $tokenId)
            ->update(['last_used_at' => now()]);
    }

    /**
     * 清理过期的 Token 和黑名单记录。
     *
     * @return int 清理的记录数
     */
    public function cleanExpiredTokens(): int
    {
        $deletedTokens = DB::table('jwt_tokens')
            ->where('expires_at', '<', now())
            ->delete();

        $deletedBlacklist = DB::table('jwt_blacklist')
            ->where('expired_at', '<', now())
            ->delete();

        return $deletedTokens + $deletedBlacklist;
    }
}
