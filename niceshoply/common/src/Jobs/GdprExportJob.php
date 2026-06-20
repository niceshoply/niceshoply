<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Address;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\CustomerPoint;
use NiceShoply\Common\Models\GdprRequest;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Models\PointLog;
use NiceShoply\Common\Models\Review;
use NiceShoply\Common\Services\Compliance\GdprService;
use Throwable;
use ZipArchive;

/**
 * GDPR 数据导出队列任务。
 */
class GdprExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(private readonly int $requestId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $request = GdprRequest::query()->find($this->requestId);
        if (! $request || $request->type !== GdprRequest::TYPE_EXPORT) {
            return;
        }

        $request->status = GdprRequest::STATUS_PROCESSING;
        $request->save();

        try {
            $customer = Customer::query()->findOrFail($request->customer_id);
            $dir      = storage_path('app/gdpr/'.$customer->id);
            File::ensureDirectoryExists($dir);

            $payload = [
                'customer'    => $customer->only(['id', 'email', 'name', 'calling_code', 'telephone', 'locale', 'created_at']),
                'addresses'   => Address::query()->where('customer_id', $customer->id)->get()->toArray(),
                'orders'      => Order::query()->where('customer_id', $customer->id)->with('items')->get()->toArray(),
                'reviews'     => Review::query()->where('customer_id', $customer->id)->get()->toArray(),
                'points'      => CustomerPoint::query()->where('customer_id', $customer->id)->first()?->toArray(),
                'point_logs'  => PointLog::query()->where('customer_id', $customer->id)->get()->toArray(),
                'exported_at' => now()->toIso8601String(),
            ];

            $jsonPath = $dir.'/export-'.$request->id.'.json';
            file_put_contents($jsonPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $zipPath = $dir.'/export-'.$request->id.'.zip';
            $zip     = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('无法创建 ZIP 文件');
            }
            $zip->addFile($jsonPath, 'customer-data.json');
            $zip->close();

            @unlink($jsonPath);

            $relative = 'gdpr/'.$customer->id.'/export-'.$request->id.'.zip';
            GdprService::getInstance()->markCompleted($request, $relative);

            activity('gdpr')
                ->performedOn($customer)
                ->withProperties(['request_id' => $request->id, 'file' => $relative])
                ->log('GDPR 数据导出完成');
        } catch (Throwable $e) {
            Log::error('GDPR 导出失败：'.$e->getMessage(), ['request_id' => $this->requestId]);
            GdprService::getInstance()->markFailed($request, $e->getMessage());
        }
    }
}
