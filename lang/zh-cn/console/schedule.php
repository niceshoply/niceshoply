<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

return [
    'title'               => '计划任务',
    'subtitle'            => '查看定时任务调度与上次执行结果，支持手动触发',
    'command'             => '命令',
    'expression'          => 'Cron 表达式',
    'description'         => '说明',
    'last_run'            => '上次执行',
    'never'               => '从未执行',
    'run_now'             => '立即执行',
    'confirm_run'         => '确定手动执行 :command 吗？',
    'run_success'         => '命令执行完成',
    'run_failed'          => '命令执行失败',
    'command_not_allowed' => '不允许执行该命令',
    'cron_hint'           => '生产环境需配置系统 cron 每分钟执行 php artisan schedule:run',
    'status_success'      => '成功',
    'status_failed'       => '失败',
    'status_manual'       => '手动',
];
