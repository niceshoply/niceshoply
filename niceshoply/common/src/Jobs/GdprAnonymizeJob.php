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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Address;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\GdprRequest;
use NiceShoply\Common\Services\Compliance\GdprService;
use Throwable;

/**
 * GDPR 账户匿名化/删除队列任务。
 */
class GdprAnonymizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(private readonly int $requestId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $request = GdprRequest::query()->find($this->requestId);
        if (! $request || $request->type !== GdprRequest::TYPE_DELETE) {
            return;
        }

        $request->status = GdprRequest::STATUS_PROCESSING;
        $request->save();

        try {
            DB::transaction(function () use ($request) {
                $customer = Customer::query()->lockForUpdate()->findOrFail($request->customer_id);

                Address::query()->where('customer_id', $customer->id)->delete();

                $anonEmail = 'deleted-'.$customer->id.'@anon.local';
                $customer->update([
                    'email'        => $anonEmail,
                    'name'         => trans('front/privacy.anonymized_name'),
                    'telephone'    => '',
                    'calling_code' => '',
                    'avatar'       => '',
                    'active'       => false,
                    'password'     => Hash::make(bin2hex(random_bytes(16))),
                    'deleted_at'   => now(),
                ]);
            });

            GdprService::getInstance()->markCompleted($request);

            activity('gdpr')
                ->withProperties(['request_id' => $request->id, 'customer_id' => $request->customer_id])
                ->log('GDPR 账户匿名化完成');
        } catch (Throwable $e) {
            Log::error('GDPR 删除失败：'.$e->getMessage(), ['request_id' => $this->requestId]);
            GdprService::getInstance()->markFailed($request, $e->getMessage());
        }
    }
}
