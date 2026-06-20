<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Horizon Metrics Snapshot
|--------------------------------------------------------------------------
|
| Horizon 仪表盘需要定时快照来展示指标图表数据，
| 每 5 分钟采集一次队列吞吐量和等待时间等关键指标。
|
*/
Schedule::command('horizon:snapshot')->everyFiveMinutes();

/*
|--------------------------------------------------------------------------
| Business Schedules
|--------------------------------------------------------------------------
|
| order:complete           已发货订单超时后自动完成
| warehouse:stock-warning  仓库低库存预警
|
| 生产环境需配置系统级 cron：
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/
Schedule::command('order:complete')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('warehouse:stock-warning')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// visit:aggregate          每日聚合访问与转化统计（聚合前一日数据）
Schedule::command('visit:aggregate')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// geoip:update             定期更新 GeoLite2 离线 IP 库（MaxMind 每周二更新数据）
// 每周三凌晨拉取，避开数据发布当天的潜在波动；--force 见命令说明。
Schedule::command('geoip:update')
    ->weeklyOn(3, '03:30')
    ->withoutOverlapping()
    ->onOneServer();

// currency:update          定期从外部 API 更新汇率
Schedule::command('currency:update')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

// abandoned-cart:scan       扫描超 N 小时未结账购物车并发送召回
Schedule::command('abandoned-cart:scan')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// seo:generate-sitemap     生成静态 sitemap.xml（含 hreflang）
Schedule::command('seo:generate-sitemap')
    ->dailyAt('05:00')
    ->withoutOverlapping()
    ->onOneServer();

// backup:run               系统完整备份（数据库 + 关键文件）
Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();
