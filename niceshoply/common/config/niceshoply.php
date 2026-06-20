<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'edition' => 'community',
    'version' => '1.0.0',
    'build'   => '20260620',
    'api_url' => env('NiceShoply_API_URL', 'https://marketplace.niceshoply.com'),

    /*
    |--------------------------------------------------------------------------
    | 在线升级（System Update）
    |--------------------------------------------------------------------------
    |
    | 主程序通过后台「系统更新」页面，对接官方升级服务器（marketplace.niceshoply.com）
    | 实现一键检查更新、下载升级包并自动覆盖、迁移、重建缓存。
    |
    */
    'upgrade' => [
        // 是否允许在线升级（部分托管环境可关闭，改用人工升级）
        'enabled' => env('NICESHOPLY_UPGRADE_ENABLED', true),

        // 升级服务器 API 路径前缀（拼接在 api_url 之后）
        'api_path' => '/api/upgrade',

        // 检查更新接口超时（秒）
        'check_timeout' => 20,

        // 下载升级包超时（秒），大包需放宽
        'download_timeout' => 1200,

        // 单次升级任务整体超时（秒），队列 Job 使用
        'job_timeout' => 1800,

        // 升级包工作目录（相对 storage/app）
        'work_dir' => 'upgrade',

        // 受保护路径：解压覆盖与删除时一律跳过，避免清掉站点数据/配置。
        // 这里的路径均相对项目根目录（base_path）。
        'protected_paths' => [
            '.env',
            '.env.example',
            'storage',
            'bootstrap/cache',
            'database/database.sqlite',
            'public/storage',
            'public/uploads',
            'public/build',
            'public/themes',
        ],

        // 进入维护模式时使用的访问密钥（持有该密钥的浏览器仍可访问站点）。
        // 升级进度查询接口已在 bootstrap/app.php 白名单放行，无需密钥也可轮询。
        'maintenance_secret' => env('NICESHOPLY_UPGRADE_SECRET', 'niceshoply-upgrade'),

        // 升级完成后是否尝试热重载常驻运行时（Octane / 队列 worker）
        'reload_runtime' => env('NICESHOPLY_UPGRADE_RELOAD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | 商业品牌授权（去版权 / 白标）
    |--------------------------------------------------------------------------
    |
    | 默认展示 Powered by NiceShoply；购买商业授权后通过应用市场 API 核验并隐藏。
    | 应用市场商品 code 须与 branding.entitlement_codes 之一匹配。
    |
    */
    'branding' => [
        'entitlement_codes' => [
            'branding-removal',
            'white-label',
            'agency-license',
            'enterprise-license',
        ],
        'cache_ttl' => (int) env('NICESHOPLY_BRANDING_CACHE_TTL', 3600),
    ],
];
