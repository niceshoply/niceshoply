<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * 商业品牌授权核验：已购买去版权/白标授权时隐藏默认 Powered by 展示。
 */

namespace NiceShoply\Common\Services\License;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Services\BaseService;

class BrandLicenseService extends BaseService
{
    /**
     * 是否可隐藏默认 NiceShoply 品牌链接。
     * fail-open：无 Token、API 失败或未购授权时仍展示 Powered by。
     */
    public function canHideBrand(): bool
    {
        if (filter_var(env('NICESHOPLY_HIDE_BRAND', false), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if (system_setting('branding_license_active', false)) {
            return true;
        }

        return $this->hasActiveBrandingEntitlement();
    }

    /**
     * 白标自定义品牌 HTML；无自定义时返回空字符串（完全隐藏）。
     */
    public function getCustomBrandHtml(): string
    {
        $name = trim((string) system_setting('custom_brand_name', ''));
        $url  = trim((string) system_setting('custom_brand_url', ''));

        if ($name !== '' && $url !== '') {
            return '<a href="'.e($url).'" class="ms-2" target="_blank" rel="noopener">'.e($name).'</a>';
        }

        return '';
    }

    /**
     * 强制刷新授权缓存（后台保存 Token 或手动刷新时调用）。
     */
    public function clearCache(): void
    {
        $token = (string) system_setting('domain_token', '');
        if ($token !== '') {
            Cache::forget($this->cacheKey($token));
        }
    }

    /**
     * 查询并返回授权状态摘要，供后台展示。
     *
     * @return array{active: bool, source: string, product_code: ?string, expires_at: ?string}
     */
    public function getStatus(): array
    {
        if (filter_var(env('NICESHOPLY_HIDE_BRAND', false), FILTER_VALIDATE_BOOLEAN)) {
            return ['active' => true, 'source' => 'env', 'product_code' => null, 'expires_at' => null];
        }

        if (system_setting('branding_license_active', false)) {
            return ['active' => true, 'source' => 'setting', 'product_code' => null, 'expires_at' => null];
        }

        $entitlement = $this->fetchEntitlement();

        return [
            'active'       => (bool) ($entitlement['active'] ?? false),
            'source'       => 'marketplace',
            'product_code' => $entitlement['product_code'] ?? null,
            'expires_at'   => $entitlement['expires_at'] ?? null,
        ];
    }

    private function hasActiveBrandingEntitlement(): bool
    {
        $token = (string) system_setting('domain_token', '');
        if ($token === '') {
            return false;
        }

        $ttl = (int) config('niceshoply.branding.cache_ttl', 3600);

        $entitlement = Cache::remember($this->cacheKey($token), $ttl, fn () => $this->fetchEntitlement());

        return (bool) ($entitlement['active'] ?? false);
    }

    /**
     * @return array{active: bool, product_code: ?string, expires_at: ?string}
     */
    private function fetchEntitlement(): array
    {
        $token = (string) system_setting('domain_token', '');
        if ($token === '') {
            return ['active' => false, 'product_code' => null, 'expires_at' => null];
        }

        try {
            $apiUrl = rtrim((string) config('niceshoply.api_url'), '/').'/api/licenses/check-entitlement';

            $response = Http::withHeaders([
                'domain-token' => $token,
                'Accept'       => 'application/json',
            ])
                ->timeout(10)
                ->get($apiUrl, ['entitlement' => 'branding']);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json('data', []);

                return [
                    'active'       => (bool) ($data['active'] ?? false),
                    'product_code' => $data['product_code'] ?? null,
                    'expires_at'   => $data['expires_at'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('BrandLicenseService: entitlement check failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return ['active' => false, 'product_code' => null, 'expires_at' => null];
    }

    private function cacheKey(string $token): string
    {
        return 'branding_license:'.md5($token);
    }
}
