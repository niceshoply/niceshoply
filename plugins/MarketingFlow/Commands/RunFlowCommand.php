<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MarketingFlow\Commands;

use Illuminate\Console\Command;
use Plugin\MarketingFlow\Services\MarketingFlowService;

class RunFlowCommand extends Command
{
    protected $signature = 'marketing:flow';

    protected $description = 'Process due marketing automation jobs and send notifications';

    public function handle(): int
    {
        $r = MarketingFlowService::getInstance()->runDue();
        $this->info("Sent: {$r['sent']}, Failed: {$r['failed']}");

        return self::SUCCESS;
    }
}
