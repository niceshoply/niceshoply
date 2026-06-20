<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use NiceShoply\Common\Repositories\SettingRepo;
use NiceShoply\Plugin\Services\MarketplaceService;
use Throwable;

class MarketplaceController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function quickCheckout(Request $request): mixed
    {
        try {
            $data = $request->all();

            return MarketplaceService::getInstance()->quickCheckout($data);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function updateDomainToken(Request $request): mixed
    {
        try {
            $domainToken = $request->get('domain_token');
            SettingRepo::getInstance()->updateSystemValue('domain_token', $domainToken);
            \NiceShoply\Common\Services\License\BrandLicenseService::getInstance()->clearCache();

            return json_success(console_trans('common.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  int  $slug
     * @return mixed
     */
    public function download(Request $request, int $slug): mixed
    {
        try {
            $type = $request->get('type', 'plugin');
            MarketplaceService::getInstance()->download($slug, $type);

            return json_success('下载成功, 请去插件或主题列表安装使用');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Get domain token from API
     *
     * @return mixed
     */
    public function getToken(): mixed
    {
        try {
            $baseUrl  = config('niceshoply.api_url');
            $response = \Illuminate\Support\Facades\Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->get('/api/domains/token');

            if ($response->successful()) {
                $data  = $response->json();
                $token = $data['data']['token'] ?? $data['data']['domain_token'] ?? null;

                if ($token) {
                    SettingRepo::getInstance()->updateSystemValue('domain_token', $token);
                }

                return json_success(console_trans('common.success'), ['token' => $token]);
            }

            return json_fail($response->json()['message'] ?? '获取 token 失败');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 接收 marketplace.niceshoply.com 回跳的 token，保存到 system_setting('domain_token')，
     * 然后向 window.opener 发送 postMessage 并自动关闭弹窗。
     *
     * GET /{console}/marketplaces/token_callback?token=xxx&existed=0|1&domain=xxx&state=xxx
     */
    public function tokenCallback(Request $request)
    {
        $token         = (string) $request->query('token', '');
        $existed       = (int) $request->query('existed', 0);
        $domain        = (string) $request->query('domain', '');
        $state         = (string) $request->query('state', '');
        $error         = (string) $request->query('error', '');
        $expectedState = (string) session('marketplace_token_state', '');

        // 防止跨用户/CSRF 攻击：state 必须匹配会话中的预期 state
        $stateValid = $state !== '' && hash_equals($expectedState, $state);

        if ($stateValid) {
            session()->forget('marketplace_token_state');

            if ($error === '' && $token !== '') {
                SettingRepo::getInstance()->updateSystemValue('domain_token', $token);
                if ($domain !== '') {
                    SettingRepo::getInstance()->updateSystemValue('domain_token_host', $domain);
                }
                \NiceShoply\Common\Services\License\BrandLicenseService::getInstance()->clearCache();
            }
        } else {
            $error = $error !== '' ? $error : 'invalid_state';
        }

        return response()->view('plugin::shared._token_callback', [
            'token'   => $token,
            'existed' => $existed,
            'domain'  => $domain,
            'error'   => $error,
            'success' => $stateValid && $error === '' && $token !== '',
        ]);
    }

    /**
     * 由前端 AJAX 调用，在打开弹窗前向后端要一个 state，并落到 session。
     * 同时返回完整的 issue URL（指向 marketplace.niceshoply.com）。
     *
     * GET /{console}/marketplaces/token_issue_url
     */
    public function tokenIssueUrl(Request $request): mixed
    {
        try {
            $state    = Str::random(40);
            $callback = console_route('marketplaces.token_callback');
            $domain   = $request->getHttpHost();
            $issueUrl = rtrim(config('niceshoply.api_url'), '/').'/domain-token/issue?'.http_build_query([
                'domain'   => $domain,
                'callback' => $callback,
                'state'    => $state,
            ]);

            session(['marketplace_token_state' => $state]);

            return json_success('ok', [
                'issue_url' => $issueUrl,
                'state'     => $state,
                'domain'    => $domain,
                'callback'  => $callback,
            ]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 刷新商业品牌授权状态（对接应用市场 check-entitlement）。
     */
    public function refreshBrandingLicense(): mixed
    {
        try {
            $service = \NiceShoply\Common\Services\License\BrandLicenseService::getInstance();
            $service->clearCache();
            $status = $service->getStatus();

            SettingRepo::getInstance()->updateSystemValue('branding_license_active', $status['active']);

            return json_success(console_trans('plugin.branding_license_refreshed'), $status);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Clear marketplace cache
     *
     * @return mixed
     */
    public function clearCache(): mixed
    {
        try {
            $driver           = config('cache.default');
            $supportedDrivers = ['redis', 'memcached', 'dynamodb'];

            if (in_array($driver, $supportedDrivers)) {
                // Use tags if supported
                try {
                    \Illuminate\Support\Facades\Cache::tags(['marketplace', 'plugin_market', 'theme_market'])->flush();
                } catch (\Exception $e) {
                    // Fallback if tags still fail
                    Log::warning('Cache tags flush failed, using prefix method', [
                        'driver' => $driver,
                        'error'  => $e->getMessage(),
                    ]);
                    $this->clearCacheByPrefix('marketplace.');
                }
            } else {
                // For drivers that don't support tags, clear by prefix
                $this->clearCacheByPrefix('marketplace.');
            }

            return json_success(console_trans('common.success'));
        } catch (\Exception $e) {
            Log::error('Failed to clear marketplace cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Clear cache by prefix (for drivers that don't support tags)
     *
     * @param  string  $prefix
     * @return void
     */
    private function clearCacheByPrefix(string $prefix): void
    {
        $driver = config('cache.default');

        try {
            if ($driver === 'database') {
                // For database driver, delete from cache table
                $cacheTable  = config('cache.stores.database.table', 'cache');
                $cachePrefix = config('cache.prefix', '');

                // Check if table exists
                if (! Schema::hasTable($cacheTable)) {
                    Log::warning('Cache table does not exist', ['table' => $cacheTable]);

                    return;
                }

                // Laravel cache keys are stored with prefix, build the full prefix
                $fullPrefix = $cachePrefix ? $cachePrefix.$prefix : $prefix;

                // Try to delete by prefix pattern
                $deleted = DB::table($cacheTable)
                    ->where('key', 'like', $fullPrefix.'%')
                    ->delete();

                // If no rows deleted, try without prefix (in case prefix is empty or different)
                if ($deleted === 0 && $cachePrefix) {
                    DB::table($cacheTable)
                        ->where('key', 'like', $prefix.'%')
                        ->delete();
                }
            } elseif ($driver === 'file') {
                // For file driver, we need to clear the entire cache directory
                // This is a limitation - file driver doesn't support pattern matching
                // We'll clear all cache as a workaround
                \Illuminate\Support\Facades\Cache::flush();
            } else {
                // For other drivers, try to flush all cache
                \Illuminate\Support\Facades\Cache::flush();
            }
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, log the error
            Log::warning('Failed to clear cache by prefix', [
                'driver' => $driver,
                'prefix' => $prefix,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
