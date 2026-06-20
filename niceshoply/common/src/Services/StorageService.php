<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Resolve the active disk name based on plugin settings.
     *
     * For file manager: returns 's3' when driver is 'oss' or 's3', otherwise 'media'.
     * For uploads: returns the specified disk directly.
     */
    public function resolveActiveDisk(string $context = 'media'): string
    {
        if ($context === 'media') {
            $driver = plugin_setting('file_manager', 'driver', 'local');

            return in_array($driver, ['oss', 's3']) ? 's3' : 'media';
        }

        return $context;
    }

    /**
     * Generate a public URL for a given storage path.
     *
     * - Paths starting with 'http' are returned as-is (already absolute URLs)
     * - Cloud disk paths use cdn_domain if configured, otherwise Storage::url()
     * - Local disk paths use asset() helper
     */
    public function url(string $path, ?string $disk = null): string
    {
        if (empty($path)) {
            return '';
        }

        // Already an absolute URL
        if (Str::startsWith($path, 'http')) {
            return $path;
        }
        $disk = $disk ?? $this->resolveActiveDisk();

        if ($this->isCloudDisk($disk)) {
            return $this->cloudUrl($path);
        }

        // Local disk — use asset() for consistency with existing code
        return asset($path);
    }

    /**
     * Check if the given disk is a cloud-based disk.
     */
    public function isCloudDisk(?string $disk = null): bool
    {
        $disk   = $disk ?? $this->resolveActiveDisk();
        $driver = config("filesystems.disks.{$disk}.driver", 'local');

        return in_array($driver, ['s3', 'oss']);
    }

    /**
     * Configure the S3/OSS disk from plugin settings.
     *
     * Consolidates the config-writing logic that was duplicated in
     * OSSService::refreshConfig() and OSSServiceProvider::register().
     */
    public function configureCloudDisk(): void
    {
        config([
            'filesystems.file_manager.driver' => plugin_setting('file_manager', 'driver', 'local'),
            'filesystems.disks.s3.key'        => plugin_setting('file_manager', 'key', ''),
            'filesystems.disks.s3.secret'     => plugin_setting('file_manager', 'secret', ''),
            'filesystems.disks.s3.endpoint'   => plugin_setting('file_manager', 'endpoint', ''),
            'filesystems.disks.s3.bucket'     => plugin_setting('file_manager', 'bucket', ''),
            'filesystems.disks.s3.region'     => plugin_setting('file_manager', 'region', ''),
            'filesystems.disks.s3.cdn_domain' => plugin_setting('file_manager', 'cdn_domain', ''),
        ]);
    }

    /**
     * Generate a cloud URL using CDN domain or endpoint fallback.
     */
    protected function cloudUrl(string $path): string
    {
        $cdnDomain = config('filesystems.disks.s3.cdn_domain', '');

        if ($cdnDomain) {
            return rtrim($cdnDomain, '/').'/'.ltrim($path, '/');
        }

        $endpoint = config('filesystems.disks.s3.endpoint', '');
        $endpoint = preg_replace('#^https?://#', '', $endpoint);

        return sprintf('https://%s/%s', $endpoint, ltrim($path, '/'));
    }
}
