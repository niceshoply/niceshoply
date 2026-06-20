<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmsMarketing\Commands;

use Illuminate\Console\Command;
use Plugin\SmsMarketing\Models\SmsCampaign;
use Plugin\SmsMarketing\Services\SmsMarketingService;

class SendCampaignCommand extends Command
{
    protected $signature = 'sms:campaign {id? : Campaign ID to send}';

    protected $description = 'Send SMS marketing campaign(s)';

    public function handle(): int
    {
        $id = $this->argument('id');
        $service = SmsMarketingService::getInstance();

        if ($id) {
            $campaign = SmsCampaign::query()->find($id);
            if (! $campaign) {
                $this->error('Campaign not found');

                return self::FAILURE;
            }
            $r = $service->sendCampaign($campaign);
            $this->info("Sent: {$r['sent']}, Fail: {$r['fail']}, Total: {$r['total']}");

            return self::SUCCESS;
        }

        $campaigns = SmsCampaign::query()->where('status', SmsCampaign::STATUS_DRAFT)->get();
        foreach ($campaigns as $campaign) {
            $r = $service->sendCampaign($campaign);
            $this->info("[{$campaign->id}] {$campaign->name}: sent {$r['sent']}/{$r['total']}");
        }

        return self::SUCCESS;
    }
}
