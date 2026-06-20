<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StockNotify\Commands;

use Illuminate\Console\Command;
use Plugin\StockNotify\Services\StockNotifyService;

class StockNotifyCommand extends Command
{
    protected $signature = 'stock:notify';

    protected $description = 'Scan restock/price-drop subscriptions and notify members';

    public function handle(): int
    {
        if (! (bool) plugin_setting('stock_notify', 'enabled', true)) {
            $this->warn('Stock notify is disabled.');

            return self::SUCCESS;
        }

        $sent = StockNotifyService::getInstance()->scanAndNotify();
        $this->info("Stock notifications sent: {$sent}");

        return self::SUCCESS;
    }
}
