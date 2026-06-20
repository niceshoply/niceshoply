<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Commands;

use Illuminate\Console\Command;
use Plugin\EmailMarketing\Services\EmailMarketingService;

class SendCampaignCommand extends Command
{
    protected $signature = 'email:campaign';

    protected $description = 'Dispatch scheduled email marketing campaigns';

    public function handle(): int
    {
        $count = EmailMarketingService::getInstance()->dispatchScheduled();
        $this->info("Dispatched {$count} scheduled campaign(s).");

        return self::SUCCESS;
    }
}
