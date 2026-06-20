<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use NiceShoply\Common\Services\Currency\CurrencyRateUpdateService;

/**
 * 汇率自动更新命令。
 *
 * 用法：php artisan currency:update
 * 可通过 .env CURRENCY_RATES_URL 指定自定义汇率 API 地址。
 */
class UpdateCurrencyRates extends Command
{
    protected $signature = 'currency:update {--base= : 基准币种代码，默认取系统默认币种}';

    protected $description = '从外部 API 拉取最新汇率并更新 currencies 表';

    public function handle(): int
    {
        if (! system_setting('currency_auto_update', true)) {
            $this->info('汇率自动更新已关闭（currency_auto_update=false），跳过。');

            return self::SUCCESS;
        }

        try {
            $result = CurrencyRateUpdateService::getInstance()->update($this->option('base'));
        } catch (Exception $e) {
            $this->error('汇率更新失败：'.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("汇率更新完成：{$result['updated']} 个币种已更新，{$result['skipped']} 个跳过。");
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }

        return self::SUCCESS;
    }
}
