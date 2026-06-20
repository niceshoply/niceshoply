<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use NiceShoply\Common\Services\AbandonedCart\AbandonedCartService;

/**
 * 扫描超 N 小时未结账的购物车并发送召回通知。
 */
class AbandonedCartScan extends Command
{
    protected $signature = 'abandoned-cart:scan';

    protected $description = '扫描弃购购物车并发送召回邮件/通知';

    public function handle(): int
    {
        if (! AbandonedCartService::getInstance()->isEnabled()) {
            $this->info('弃购挽回未启用，跳过扫描。');

            return self::SUCCESS;
        }

        $result = AbandonedCartService::getInstance()->scanAndRemind();

        $this->info(sprintf(
            '扫描完成：共 %d 组购物车，发送 %d 条召回，跳过 %d 组。',
            $result['scanned'],
            $result['reminded'],
            $result['skipped']
        ));

        return self::SUCCESS;
    }
}
