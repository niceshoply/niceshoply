<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use NiceShoply\Common\Models\Visit\Visit;

/**
 * 访问数据富集服务
 *
 * VisitTrackingService 在写入时已做实时富集；本服务用于对
 * 历史/缺失数据做补全（地理位置、浏览器、操作系统），
 * 供后台「补全访问数据」按钮或运维脚本调用。
 */
class VisitEnrichService
{
    /**
     * 补全单条访问记录的地理位置。
     *
     * @param  Visit  $visit
     * @return array{success: bool, country_name: string, city: string}
     */
    public function locate(Visit $visit): array
    {
        $service = new GeoLocationService;
        $result  = $service->getLocation($visit->ip_address);

        $visit->update([
            'country_code' => $result['country_code'] ?? '',
            'country_name' => $result['country_name'] ?? '',
            'city'         => $result['city'] ?? '',
        ]);

        return [
            'success'      => true,
            'country_name' => $result['country_name'] ?? '',
            'city'         => $result['city'] ?? '',
        ];
    }

    /**
     * 根据 user_agent 补全单条访问记录的浏览器/操作系统。
     *
     * @param  Visit  $visit
     * @return array{success: bool, browser: string, os: string}
     */
    public function parseUA(Visit $visit): array
    {
        $browser = self::detectBrowser((string) $visit->user_agent);
        $os      = self::detectOS((string) $visit->user_agent);

        $visit->update([
            'browser' => $browser,
            'os'      => $os,
        ]);

        return [
            'success' => true,
            'browser' => $browser,
            'os'      => $os,
        ];
    }

    /**
     * 批量补全缺失数据的访问记录（地理 + UA），单次最多处理 500 条。
     *
     * @return array{success: bool, updated: int}
     */
    public function batchLocate(): array
    {
        $geoService = new GeoLocationService;

        // 筛选地理或设备字段为空的记录
        $visits = Visit::where(function ($q) {
            $q->whereNull('country_name')
                ->orWhere('country_name', '')
                ->orWhereNull('city')
                ->orWhere('city', '')
                ->orWhereNull('browser')
                ->orWhere('browser', '')
                ->orWhereNull('os')
                ->orWhere('os', '');
        })
            ->limit(500)
            ->get();

        $updated = 0;

        foreach ($visits as $visit) {
            $fields = [];

            // 仅在地理字段缺失且有 IP 时查询，减少无谓调用
            if ($visit->ip_address && (empty($visit->country_name) || empty($visit->city))) {
                $result = $geoService->getLocation($visit->ip_address);
                if (! empty($result['country_name']) || ! empty($result['city'])) {
                    $fields['country_code'] = $result['country_code'] ?? '';
                    $fields['country_name'] = $result['country_name'] ?? '';
                    $fields['city']         = $result['city'] ?? '';
                }
            }

            // 设备字段缺失且有 UA 时解析
            if ($visit->user_agent && (empty($visit->browser) || empty($visit->os))) {
                $fields['browser'] = self::detectBrowser($visit->user_agent);
                $fields['os']      = self::detectOS($visit->user_agent);
            }

            if (! empty($fields)) {
                $visit->update($fields);
                $updated++;
            }
        }

        return [
            'success' => true,
            'updated' => $updated,
        ];
    }

    /**
     * 从 user_agent 识别浏览器名称。
     *
     * @param  string  $ua
     * @return string
     */
    public static function detectBrowser(string $ua): string
    {
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
     * 从 user_agent 识别操作系统名称。
     *
     * @param  string  $ua
     * @return string
     */
    public static function detectOS(string $ua): string
    {
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
