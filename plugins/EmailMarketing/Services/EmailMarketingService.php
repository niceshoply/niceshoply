<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Services;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use NiceShoply\Common\Models\Customer;
use Plugin\EmailMarketing\Models\EmailCampaign;
use Plugin\EmailMarketing\Models\EmailSubscriber;

class EmailMarketingService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 订阅邮件（前台）。
     */
    public function subscribe(string $email, int $customerId = 0): EmailSubscriber
    {
        $email = strtolower(trim($email));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(__('EmailMarketing::common.invalid_email'));
        }

        /** @var EmailSubscriber $sub */
        $sub = EmailSubscriber::query()->firstOrNew(['email' => $email]);
        $sub->customer_id = $customerId ?: ($sub->customer_id ?? 0);
        $sub->subscribed  = true;
        if (! $sub->token) {
            $sub->token = Str::random(40);
        }
        $sub->save();

        return $sub;
    }

    public function unsubscribeByToken(string $token): bool
    {
        $sub = EmailSubscriber::query()->where('token', $token)->first();
        if (! $sub) {
            return false;
        }
        $sub->subscribed = false;
        $sub->save();

        return true;
    }

    /**
     * 发送一个邮件活动（分批，带退订链接）。
     *
     * @return array{sent:int, fail:int, total:int}
     */
    public function sendCampaign(EmailCampaign $campaign): array
    {
        if ($campaign->status === EmailCampaign::STATUS_SENT) {
            return ['sent' => $campaign->sent_count, 'fail' => $campaign->fail_count, 'total' => $campaign->total];
        }

        $recipients = $this->recipients($campaign->target);
        $batchSize  = max(1, (int) plugin_setting('email_marketing', 'batch_size', 50));
        $fromName   = (string) plugin_setting('email_marketing', 'from_name', '');

        $campaign->status = EmailCampaign::STATUS_SENDING;
        $campaign->total  = count($recipients);
        $campaign->save();

        $sent = 0;
        $fail = 0;

        foreach (array_chunk($recipients, $batchSize) as $chunk) {
            foreach ($chunk as $r) {
                try {
                    $html = $this->renderBody($campaign->body, $r['token'] ?? '');
                    Mail::html($html, function ($message) use ($r, $campaign, $fromName) {
                        $message->to($r['email'])->subject($campaign->subject);
                        if ($fromName !== '') {
                            $message->from(config('mail.from.address'), $fromName);
                        }
                    });
                    $sent++;
                } catch (\Throwable $e) {
                    $fail++;
                    Log::warning('email_marketing.send.failed', ['email' => $r['email'], 'error' => $e->getMessage()]);
                }
            }
            $campaign->sent_count = $sent;
            $campaign->fail_count = $fail;
            $campaign->save();
        }

        $campaign->status  = EmailCampaign::STATUS_SENT;
        $campaign->sent_at = now();
        $campaign->save();

        return ['sent' => $sent, 'fail' => $fail, 'total' => $campaign->total];
    }

    /**
     * 处理到期的定时活动（命令调用）。
     */
    public function dispatchScheduled(): int
    {
        $count = 0;
        EmailCampaign::query()
            ->where('status', EmailCampaign::STATUS_DRAFT)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->get()
            ->each(function (EmailCampaign $c) use (&$count) {
                $this->sendCampaign($c);
                $count++;
            });

        return $count;
    }

    /**
     * @return array<int, array{email:string, token:string}>
     */
    protected function recipients(string $target): array
    {
        if ($target === 'customers') {
            return Customer::query()
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get(['email'])
                ->map(fn ($c) => ['email' => $c->email, 'token' => ''])
                ->all();
        }

        return EmailSubscriber::query()
            ->where('subscribed', true)
            ->get(['email', 'token'])
            ->map(fn ($s) => ['email' => $s->email, 'token' => $s->token])
            ->all();
    }

    protected function renderBody(string $body, string $token): string
    {
        if ($token === '') {
            return $body;
        }

        $url  = url('/email/unsubscribe/'.$token);
        $foot = '<hr><p style="font-size:12px;color:#999">'
            .__('EmailMarketing::common.unsubscribe_tip')
            .' <a href="'.$url.'">'.__('EmailMarketing::common.unsubscribe').'</a></p>';

        return $body.$foot;
    }
}
