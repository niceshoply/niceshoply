<?php

namespace NiceShoply\RestAPI\Providers;

use Illuminate\Support\ServiceProvider;
use NiceShoply\RestAPI\Services\OSSService;

class OSSServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (plugin_setting('file_manager', 'driver') === 'oss') {
            config([
                'filesystems.file_manager.driver' => 'oss',
                'filesystems.disks.s3.key'        => plugin_setting('file_manager', 'key'),
                'filesystems.disks.s3.secret'     => plugin_setting('file_manager', 'secret'),
                'filesystems.disks.s3.endpoint'   => plugin_setting('file_manager', 'endpoint'),
                'filesystems.disks.s3.bucket'     => plugin_setting('file_manager', 'bucket'),
                'filesystems.disks.s3.region'     => plugin_setting('file_manager', 'region'),
                'filesystems.disks.s3.cdn_domain' => plugin_setting('file_manager', 'cdn_domain'),
            ]);
        }

        if (! $this->app->bound('filesystem')) {
            $this->app->singleton('filesystem', function ($app) {
                return $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            });
        }
    }

    public function boot()
    {
        if (plugin_setting('file_manager', 'driver') === 'oss') {
            $this->app->singleton('file_manager.service', function ($app) {
                return new OSSService;
            });
        }
    }
}
