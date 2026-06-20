<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\OrderRepo;
use Rap2hpoutre\FastExcel\FastExcel;

class ExportOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        private readonly array $filters,
        private readonly string $filePath,
        private readonly string $locale
    ) {}

    public function handle(): void
    {
        app()->setLocale($this->locale);

        $orders     = OrderRepo::getInstance()->builder($this->filters)->get();
        $exportData = collect();

        foreach ($orders as $order) {
            $exportData->push([
                trans('console/order.number')                 => $order->number,
                trans('console/order.created_at')             => $order->created_at,
                trans('console/order.customer_name')          => $order->customer_name ?? ($order->customer->name ?? ''),
                trans('console/order.status')                 => $order->status_format ?? $order->status,
                trans('console/order.total')                  => $order->total_format,
                trans('console/order.billing_method_name')    => $order->billing_method_name,
                trans('console/order.shipping_method_name')   => $order->shipping_method_name,
                trans('console/order.shipping_customer_name') => $order->shipping_customer_name,
                trans('console/order.shipping_telephone')     => $order->shipping_telephone,
                trans('console/order.shipping_address')       => $order->shipping_address_1.' '.$order->shipping_city.' '.$order->shipping_state.' '.$order->shipping_country,
                trans('console/order.comment')                => $order->comment,
            ]);
        }

        (new FastExcel($exportData))->export($this->filePath);

        Log::info("Order export completed: {$orders->count()} orders exported to {$this->filePath}");
    }
}
