<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use NiceShoply\Common\Services\GeoLite2Service;

/**
 * GeoLite2 离线 IP 库更新命令
 *
 * 用途：
 * - 部署后首次拉取 GeoLite2-City 数据库，落地到 storage/app/geolite2/
 * - 配合调度器定期更新（MaxMind 每周二更新数据，建议每周拉取一次）
 *
 * 用法：
 * - php artisan geoip:update
 *     使用默认下载源（res.innoshop.net 镜像）或 .env 中 GEOLITE2_DOWNLOAD_URL 指定地址。
 * - php artisan geoip:update --url=https://your-host/GeoLite2-City.mmdb
 *     指定自定义下载地址（如 MaxMind 官方带 license_key 的下载直链）。
 * - php artisan geoip:update --force
 *     即使本地已存在且为近期文件，也强制重新下载。
 *
 * MaxMind 官方下载（需注册免费账号获取 license_key）：
 *   https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=YOUR_KEY&suffix=tar.gz
 *   注意官方为 tar.gz 打包，需解压取出 .mmdb；本命令直接接收 .mmdb 直链，
 *   若使用官方 tar.gz，请改用 scripts/geoip-update.sh 完成解压再落盘。
 */
class GeoLite2Update extends Command
{
    protected $signature = 'geoip:update
        {--url= : 自定义 .mmdb 下载地址（缺省读取 GEOLITE2_DOWNLOAD_URL 或内置镜像）}
        {--force : 即使本地库为近期文件也强制重新下载}';

    protected $description = '下载 / 更新 GeoLite2-City 离线 IP 库到 storage/app/geolite2/';

    public function handle(): int
    {
        $service = new GeoLite2Service;

        // 非强制模式下，若本地库存在且更新于 7 天内则跳过，避免重复拉取
        if (! $this->option('force')) {
            $info = $service->getDatabaseInfo();
            if ($info['exists'] && $info['modified'] > 0 && (time() - $info['modified']) < 7 * 86400) {
                $this->info('GeoLite2 库已是近期文件（'.$info['modified_formatted'].'），跳过下载。使用 --force 可强制更新。');

                return self::SUCCESS;
            }
        }

        $url = $this->option('url') ?: env('GEOLITE2_DOWNLOAD_URL') ?: null;

        $this->info('开始下载 GeoLite2-City 数据库'.($url ? "（{$url}）" : '（默认源）').' …');

        $result = $service->downloadDatabase($url);

        if (! ($result['success'] ?? false)) {
            $this->error('下载失败：'.($result['message'] ?? '未知错误'));

            return self::FAILURE;
        }

        $info = $service->getDatabaseInfo();
        $this->info('下载成功：'.($result['path'] ?? $service->getDatabasePath()));
        $this->line('  大小：'.$info['size_formatted'].'，版本：'.($info['version'] ?: '-').'，更新时间：'.$info['modified_formatted']);

        return self::SUCCESS;
    }
}
