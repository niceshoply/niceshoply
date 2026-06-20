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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeoLite2 离线 IP 库管理服务
 *
 * 负责下载、校验、查询本地 GeoLite2-City 数据库文件。
 */
class GeoLite2Service
{
    /**
     * 数据库存放目录。
     */
    private string $storagePath;

    /**
     * 数据库文件路径。
     */
    private string $databasePath;

    /**
     * 默认下载地址（可通过参数覆盖）。
     */
    private string $defaultDownloadUrl = 'https://res.innoshop.net/GeoLite2-City.mmdb';

    public function __construct()
    {
        $this->storagePath  = storage_path('app/geolite2');
        $this->databasePath = $this->storagePath.'/GeoLite2-City.mmdb';

        if (! File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }
    }

    /**
     * 下载 GeoLite2 数据库。
     *
     * @param  string|null  $url
     * @return array
     */
    public function downloadDatabase(?string $url = null): array
    {
        try {
            $url = $url ?: $this->defaultDownloadUrl;

            if (empty($url)) {
                return ['success' => false, 'message' => __('console/setting_geolite2.download_url_empty')];
            }

            $response = Http::timeout(300)->get($url);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => __('console/setting_geolite2.download_failed', ['error' => 'HTTP '.$response->status()]),
                ];
            }

            $content = $response->body();

            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => __('console/setting_geolite2.download_failed', ['error' => __('console/setting_geolite2.download_empty')]),
                ];
            }

            File::put($this->databasePath, $content);

            // 校验数据库文件可用性
            try {
                $reader = new Reader($this->databasePath);
                $reader->city('8.8.8.8');
                $reader = null;
            } catch (Exception $e) {
                File::delete($this->databasePath);

                return [
                    'success' => false,
                    'message' => __('console/setting_geolite2.download_failed', [
                        'error' => __('console/setting_geolite2.verify_failed', ['error' => $e->getMessage()]),
                    ]),
                ];
            }

            return [
                'success' => true,
                'message' => __('console/setting_geolite2.download_success'),
                'path'    => $this->databasePath,
            ];
        } catch (Exception $e) {
            Log::error('GeoLite2Service: 下载数据库失败', ['url' => $url, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => __('console/setting_geolite2.download_failed', ['error' => $e->getMessage()]),
            ];
        }
    }

    /**
     * 获取数据库信息。
     *
     * @return array
     */
    public function getDatabaseInfo(): array
    {
        clearstatcache(true, $this->databasePath);

        $exists   = File::exists($this->databasePath);
        $size     = $exists ? File::size($this->databasePath) : 0;
        $modified = $exists ? File::lastModified($this->databasePath) : 0;

        $version = '';

        if ($exists) {
            try {
                $reader   = new Reader($this->databasePath);
                $metadata = $reader->metadata();
                $version  = $metadata->databaseType ?? '';
                $reader   = null;
            } catch (Exception $e) {
                Log::warning('GeoLite2Service: 读取元数据失败', ['error' => $e->getMessage()]);
            }
        }

        return [
            'exists'             => $exists,
            'size'               => $size,
            'size_formatted'     => $this->formatBytes($size),
            'modified'           => $modified,
            'modified_formatted' => $modified ? date('Y-m-d H:i:s', $modified) : '-',
            'version'            => $version,
            'path'               => $this->databasePath,
        ];
    }

    /**
     * 数据库是否可用。
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return File::exists($this->databasePath);
    }

    /**
     * 获取数据库路径。
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        return $this->databasePath;
    }

    /**
     * 字节数格式化为可读字符串。
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
