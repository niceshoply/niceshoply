<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Services\Upgrade\UpgradeService;
use Throwable;

/**
 * 系统在线升级队列任务
 *
 * 在后台 worker 中执行完整升级流程（下载 → 校验 → 覆盖 → 迁移 → 缓存 → 重载）。
 * 升级期间会进入维护模式，进度实时写入缓存，由后台页面轮询展示。
 */
class UpgradeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** 失败不重试，避免重复覆盖文件导致状态错乱 */
    public int $tries = 1;

    /** 升级流程整体超时（秒） */
    public int $timeout;

    /**
     * @param  array  $release  check() 返回的版本数据节点
     */
    public function __construct(private readonly array $release)
    {
        $this->timeout = (int) config('niceshoply.upgrade.job_timeout', 1800);
    }

    /**
     * 执行升级。
     */
    public function handle(): void
    {
        UpgradeService::getInstance()->perform($this->release);
    }

    /**
     * 队列任务彻底失败（异常未被 perform 捕获 / 超时）时回调。
     */
    public function failed(?Throwable $e): void
    {
        $message = $e?->getMessage() ?? 'unknown error';
        Log::error('UpgradeJob failed: '.$message);

        UpgradeService::getInstance()->markFailed($message);
    }
}
