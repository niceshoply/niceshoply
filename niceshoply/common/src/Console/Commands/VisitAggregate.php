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
use NiceShoply\Common\Services\VisitStatisticsService;

/**
 * 访问与转化每日聚合命令
 *
 * 用法：
 * - php artisan visit:aggregate              聚合昨日数据
 * - php artisan visit:aggregate --date=2026-06-11   聚合指定日期
 */
class VisitAggregate extends Command
{
    protected $signature = 'visit:aggregate {--date= : 指定聚合日期（Y-m-d），默认昨日}';

    protected $description = '聚合访问统计与转化漏斗的每日数据';

    public function handle(): void
    {
        $date = $this->option('date');

        $this->info('开始聚合访问与转化每日统计'.($date ? "（{$date}）" : '（昨日）'));

        app(VisitStatisticsService::class)->aggregateDaily($date);

        $this->info('聚合完成');
    }
}
