<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Subscription\Commands;

use Illuminate\Console\Command;
use Plugin\Subscription\Services\SubscriptionService;

class RunSubscriptionCommand extends Command
{
    protected $signature = 'subscription:run';

    protected $description = 'Generate orders for due subscriptions';

    public function handle(): int
    {
        $stats = SubscriptionService::getInstance()->runDue();

        $this->info(sprintf(
            'Subscriptions processed: %d, auto-paid: %d, failed: %d',
            $stats['processed'],
            $stats['paid'],
            $stats['failed']
        ));

        return self::SUCCESS;
    }
}
