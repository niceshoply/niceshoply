<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmsMarketing\Services;

use NiceShoply\Common\Models\Customer;
use Plugin\NotifyCenter\Services\SmsService;
use Plugin\SmsMarketing\Models\SmsCampaign;
use Plugin\SmsMarketing\Models\SmsUnsubscribe;
use RuntimeException;

class SmsMarketingService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('sms_marketing', 'enabled', true);
    }

    protected function batchSize(): int
    {
        return max(1, min((int) plugin_setting('sms_marketing', 'batch_size', 50), 500));
    }

    /**
     * 获取待发送手机号（排除退订）。
     */
    public function recipients(string $target = 'customers'): array
    {
        $blocked = SmsUnsubscribe::query()->pluck('mobile')->all();

        $query = Customer::query()->whereNotNull('telephone')->where('telephone', '!=', '');
        if (! empty($blocked)) {
            $query->whereNotIn('telephone', $blocked);
        }

        return $query->pluck('telephone')->unique()->filter()->values()->all();
    }

    /**
     * 发送一个短信活动。
     *
     * @return array{sent:int, fail:int, total:int}
     */
    public function sendCampaign(SmsCampaign $campaign): array
    {
        if (! $this->enabled()) {
            throw new RuntimeException(__('SmsMarketing::common.disabled'));
        }
        if (! class_exists(SmsService::class) || ! SmsService::ready()) {
            throw new RuntimeException(__('SmsMarketing::common.no_sms'));
        }

        if ($campaign->status === SmsCampaign::STATUS_SENT) {
            return ['sent' => $campaign->sent_count, 'fail' => $campaign->fail_count, 'total' => $campaign->total];
        }

        $mobiles = $this->recipients($campaign->target);
        $campaign->status = SmsCampaign::STATUS_SENDING;
        $campaign->total  = count($mobiles);
        $campaign->save();

        $sms     = SmsService::getInstance();
        $data    = $campaign->template_data ?? [];
        $sent    = 0;
        $fail    = 0;
        $batch   = $this->batchSize();

        foreach (array_chunk($mobiles, $batch) as $chunk) {
            foreach ($chunk as $mobile) {
                if ($sms->send($mobile, $campaign->template_id, $data)) {
                    $sent++;
                } else {
                    $fail++;
                }
            }
            usleep(200000); // 200ms 间隔，避免网关限流
        }

        $campaign->sent_count = $sent;
        $campaign->fail_count = $fail;
        $campaign->status     = SmsCampaign::STATUS_SENT;
        $campaign->sent_at    = now();
        $campaign->save();

        return ['sent' => $sent, 'fail' => $fail, 'total' => count($mobiles)];
    }

    public function unsubscribe(string $mobile): bool
    {
        $mobile = preg_replace('/\D+/', '', trim($mobile));
        if ($mobile === '') {
            return false;
        }

        SmsUnsubscribe::query()->firstOrCreate(['mobile' => $mobile], ['created_at' => now()]);

        return true;
    }
}
