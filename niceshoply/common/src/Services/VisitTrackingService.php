<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Detection\MobileDetect;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Visit\Visit;

/**
 * 访问追踪服务
 *
 * 记录会话级访问信息：IP、地理位置、设备、浏览器、操作系统、来源等。
 */
class VisitTrackingService
{
    /**
     * 地理位置服务实例。
     */
    private GeoLocationService $geoLocationService;

    /**
     * UA 解析器。
     */
    private MobileDetect $detect;

    public function __construct()
    {
        $this->geoLocationService = new GeoLocationService;
        $this->detect             = new MobileDetect;
    }

    /**
     * 追踪一次访问。
     *
     * @param  Request  $request
     * @param  string  $sessionId
     * @param  int|null  $customerId
     * @return Visit|null
     */
    public function trackVisit(Request $request, string $sessionId, ?int $customerId = null): ?Visit
    {
        try {
            if ($this->shouldSkipTracking($request)) {
                return null;
            }

            $this->detect->setUserAgent($request->userAgent() ?? '');

            $ip       = $this->getClientIp($request);
            $location = $this->geoLocationService->getLocation($ip);

            // 单表设计：每会话一条记录
            $visit = Visit::where('session_id', $sessionId)->first();

            if ($visit) {
                $updateData = [
                    'last_visited_at' => now(),
                    'customer_id'     => $customerId ?: $visit->customer_id,
                ];

                if (empty($visit->browser) || empty($visit->os)) {
                    $updateData['browser'] = $this->getBrowser();
                    $updateData['os']      = $this->getOperatingSystem();
                }

                $visit->update($updateData);
            } else {
                $visit = Visit::create([
                    'session_id'       => $sessionId,
                    'customer_id'      => $customerId,
                    'ip_address'       => $ip,
                    'user_agent'       => $request->userAgent(),
                    'country_code'     => $location['country_code'],
                    'country_name'     => $location['country_name'],
                    'city'             => $location['city'],
                    'referrer'         => $request->header('referer'),
                    'device_type'      => $this->getDeviceType(),
                    'browser'          => $this->getBrowser(),
                    'os'               => $this->getOperatingSystem(),
                    'locale'           => front_locale_code(),
                    'first_visited_at' => now(),
                    'last_visited_at'  => now(),
                ]);
            }

            return $visit;
        } catch (Exception $e) {
            Log::error('VisitTrackingService: 追踪访问失败', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 是否跳过追踪。
     *
     * @param  Request  $request
     * @return bool
     */
    private function shouldSkipTracking(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        // 跳过 API 与后台路由
        if (str_starts_with($request->path(), 'api/') || str_starts_with($request->path(), 'console/')) {
            return true;
        }

        $excludedRoutes = [
            'carts.mini',
            'countries.index',
            'countries.show',
        ];

        return in_array($routeName, $excludedRoutes, true);
    }

    /**
     * 获取客户端 IP。
     *
     * @param  Request  $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        $ip = $request->ip();

        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        return $ip;
    }

    /**
     * 获取设备类型。
     *
     * @return string
     */
    private function getDeviceType(): string
    {
        if ($this->detect->isMobile()) {
            return 'mobile';
        }

        if ($this->detect->isTablet()) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * 从 UA 解析浏览器名称。
     *
     * @return string
     */
    private function getBrowser(): string
    {
        $ua = $this->detect->getUserAgent() ?? '';

        $patterns = [
            'Edg/'            => 'Edge',
            'OPR/'            => 'Opera',
            'Opera'           => 'Opera',
            'Vivaldi/'        => 'Vivaldi',
            'Brave/'          => 'Brave',
            'SamsungBrowser/' => 'Samsung Browser',
            'UCBrowser/'      => 'UC Browser',
            'MicroMessenger/' => 'WeChat',
            'QQBrowser/'      => 'QQ Browser',
            'Firefox/'        => 'Firefox',
            'FxiOS/'          => 'Firefox',
            'Chrome/'         => 'Chrome',
            'CriOS/'          => 'Chrome',
            'Safari/'         => 'Safari',
            'MSIE'            => 'IE',
            'Trident/'        => 'IE',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }

    /**
     * 从 UA 解析操作系统名称。
     *
     * @return string
     */
    private function getOperatingSystem(): string
    {
        $ua = $this->detect->getUserAgent() ?? '';

        $patterns = [
            'HarmonyOS'     => 'HarmonyOS',
            'Android'       => 'Android',
            'iPhone'        => 'iOS',
            'iPad'          => 'iPadOS',
            'iPod'          => 'iOS',
            'Windows Phone' => 'Windows Phone',
            'Windows NT'    => 'Windows',
            'Mac OS X'      => 'macOS',
            'Macintosh'     => 'macOS',
            'Linux'         => 'Linux',
            'CrOS'          => 'Chrome OS',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }
}
