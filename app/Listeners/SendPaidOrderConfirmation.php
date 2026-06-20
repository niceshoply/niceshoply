<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Events\OrderPaid;

/**
 * 订单支付成功后的异步监听（队列化）。
 *
 * 这是「核心业务流程引入 Laravel Event/Listener」的示例落点：把支付成功后的
 * 副作用（确认通知、财务记账、对账推送等）从同步主链路解耦到队列，
 * 与 Hook 扩展机制互补。当前实现记录结构化日志，后续可在此发送通知 / 入账。
 */
class SendPaidOrderConfirmation implements ShouldQueue
{
    /**
     * 指定队列，便于 Horizon 单独限流 / 监控。
     */
    public string $queue = 'notifications';

    public function handle(OrderPaid $event): void
    {
        Log::channel('order')->info('event.order_paid.handled', [
            'order_id'     => $event->order->id,
            'order_number' => $event->order->number,
            'total'        => $event->order->total,
        ]);
    }
}
