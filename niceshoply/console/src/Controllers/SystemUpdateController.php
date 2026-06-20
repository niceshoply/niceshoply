<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use NiceShoply\Common\Services\Upgrade\UpgradeService;

/**
 * 后台系统在线更新控制器
 *
 * 提供检查更新、启动升级（投递队列）、查询升级进度等入口，
 * 实际升级逻辑由 {@see UpgradeService} 与 {@see \NiceShoply\Common\Jobs\UpgradeJob} 完成。
 */
class SystemUpdateController
{
    /**
     * 系统更新首页。
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $service = UpgradeService::getInstance();

        $data = [
            'enabled'              => $service->isEnabled(),
            'current_version'      => $service->getCurrentVersion(),
            'current_build'        => $service->getCurrentBuild(),
            'current_edition'      => $service->getCurrentEdition(),
            'has_domain_token'     => ! empty(system_setting('domain_token')),
            'progress'             => $service->getProgress(),
            'is_running'           => $service->isRunning(),
            'last_upgrade_version' => system_setting('app_last_upgrade_version'),
            'last_upgrade_at'      => system_setting('app_last_upgrade_at'),
        ];

        return nice_view('console::system_update.index', $data);
    }

    /**
     * 检查官方是否有新版本。
     *
     * @return mixed
     */
    public function check(): mixed
    {
        $result = UpgradeService::getInstance()->check();

        if (! ($result['success'] ?? false)) {
            return json_fail($result['error'] ?? trans('console/system_update.check_failed', ['code' => 0]));
        }

        return json_success(trans('console/system_update.check_done'), [
            'has_update' => $result['has_update'] ?? false,
            'release'    => $result['data'] ?? [],
        ]);
    }

    /**
     * 启动升级：服务端重新校验最新版本后投递队列任务。
     *
     * @return mixed
     */
    public function start(): mixed
    {
        try {
            $service = UpgradeService::getInstance();

            if ($service->isRunning()) {
                return json_fail(trans('console/system_update.already_running'));
            }

            // 服务端重新检查，确保升级目标来自官方而非前端篡改
            $result = $service->check();
            if (! ($result['success'] ?? false)) {
                return json_fail($result['error'] ?? trans('console/system_update.check_failed', ['code' => 0]));
            }
            if (! ($result['has_update'] ?? false)) {
                return json_fail(trans('console/system_update.no_update'));
            }

            $service->queue($result['data'] ?? []);

            return json_success(trans('console/system_update.start_queued'), [
                'version' => $result['data']['latest_version'] ?? $result['data']['version'] ?? '',
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 查询升级进度（升级期间该路由在维护模式下仍可访问）。
     *
     * @return mixed
     */
    public function progress(): mixed
    {
        return json_success('', UpgradeService::getInstance()->getProgress());
    }

    /**
     * 查询升级日志（进度内日志的精简视图）。
     *
     * @return mixed
     */
    public function log(): mixed
    {
        $progress = UpgradeService::getInstance()->getProgress();

        return json_success('', [
            'status' => $progress['status'] ?? 'idle',
            'logs'   => $progress['logs'] ?? [],
        ]);
    }
}
