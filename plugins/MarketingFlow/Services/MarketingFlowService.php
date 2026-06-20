<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MarketingFlow\Services;

use Plugin\MarketingFlow\Models\Flow;
use Plugin\MarketingFlow\Models\FlowJob;
use Plugin\NotifyCenter\Services\NotifyService;

class MarketingFlowService
{
    public const EVENTS = ['register', 'order_placed', 'order_paid'];

    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('marketing_flow', 'enabled', true);
    }

    /**
     * 事件触发：为命中规则的会员入队触达任务。
     *
     * @param  array<string,string>  $vars  模板变量，如 ['order_no' => 'xxx']
     */
    public function trigger(string $event, int $customerId, array $vars = []): void
    {
        if (! $this->enabled() || $customerId <= 0 || ! in_array($event, self::EVENTS, true)) {
            return;
        }

        $flows = Flow::query()->where('trigger_event', $event)->where('is_active', true)->get();
        foreach ($flows as $flow) {
            FlowJob::query()->create([
                'flow_id'     => $flow->id,
                'customer_id' => $customerId,
                'title'       => $this->render($flow->title, $vars),
                'content'     => $this->render((string) $flow->content, $vars),
                'run_at'      => now()->addMinutes((int) $flow->delay_minutes),
                'status'      => 'pending',
            ]);
        }
    }

    protected function render(string $tpl, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $tpl = str_replace('{'.$k.'}', (string) $v, $tpl);
        }

        return $tpl;
    }

    /**
     * 处理到点的触达任务。
     *
     * @return array{sent:int, failed:int}
     */
    public function runDue(int $limit = 500): array
    {
        $sent = 0;
        $failed = 0;

        $jobs = FlowJob::query()
            ->where('status', 'pending')
            ->where('run_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($jobs as $job) {
            try {
                if (class_exists(NotifyService::class)) {
                    NotifyService::getInstance()->notify(
                        (int) $job->customer_id,
                        $job->title,
                        (string) $job->content,
                        'marketing'
                    );
                }
                $job->update(['status' => 'sent']);
                Flow::query()->whereKey($job->flow_id)->increment('sent_count');
                $sent++;
            } catch (\Throwable $e) {
                $job->update(['status' => 'failed']);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
