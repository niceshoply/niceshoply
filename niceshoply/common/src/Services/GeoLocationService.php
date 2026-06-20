<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * IP 地理位置解析服务
 *
 * 优先使用本地 GeoLite2 库；失败时通过 geo_location.lookup Hook 让插件接管远程查询。
 */
class GeoLocationService
{
    /**
     * GeoLite2 数据库读取器实例。
     */
    private ?Reader $reader = null;

    /**
     * GeoLite2 服务实例。
     */
    private GeoLite2Service $geoLite2Service;

    public function __construct()
    {
        $this->geoLite2Service = new GeoLite2Service;
    }

    /**
     * 根据 IP 获取地理位置信息。
     *
     * @param  string  $ip
     * @return array
     */
    public function getLocation(string $ip): array
    {
        $result = $this->lookupLocalDatabase($ip);

        if (empty($result['country_name']) && empty($result['city'])) {
            $result['ip'] = $ip;
            $result       = fire_hook_filter('geo_location.lookup', $result);
            unset($result['ip']);
        }

        return $result;
    }

    /**
     * 从本地 GeoIP 库查询。
     *
     * @param  string  $ip
     * @return array
     */
    private function lookupLocalDatabase(string $ip): array
    {
        $databasePath = $this->geoLite2Service->getDatabasePath();

        if (empty($databasePath) || ! File::exists($databasePath)) {
            return $this->getDefaultLocation();
        }

        try {
            if ($this->reader === null) {
                $this->reader = new Reader($databasePath);
            }

            $record = $this->reader->city($ip);

            return [
                'country_code' => $record->country->isoCode ?? '',
                'country_name' => $record->country->name ?? '',
                'city'         => $record->city->name ?? '',
                'latitude'     => $record->location->latitude ?? null,
                'longitude'    => $record->location->longitude ?? null,
            ];
        } catch (AddressNotFoundException) {
            return $this->getDefaultLocation();
        } catch (Exception $e) {
            Log::warning('GeoLocationService: 获取地理位置失败', ['ip' => $ip, 'error' => $e->getMessage()]);

            return $this->getDefaultLocation();
        }
    }

    /**
     * 默认（空）地理位置。
     *
     * @return array
     */
    private function getDefaultLocation(): array
    {
        return [
            'country_code' => '',
            'country_name' => '',
            'city'         => '',
            'latitude'     => null,
            'longitude'    => null,
        ];
    }

    /**
     * GeoLite2 库是否可用。
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->geoLite2Service->isAvailable();
    }
}
