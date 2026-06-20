<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Compliance;

use Exception;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Jobs\GdprAnonymizeJob;
use NiceShoply\Common\Jobs\GdprExportJob;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\GdprRequest;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\GdprRequestRepo;
use NiceShoply\Common\Services\BaseService;

/**
 * GDPR 数据导出与删除申请服务。
 */
class GdprService extends BaseService
{
    /**
     * 提交数据导出申请。
     */
    public function requestExport(Customer $customer, string $ip = ''): GdprRequest
    {
        if (GdprRequestRepo::getInstance()->findPendingExport($customer->id)) {
            throw new Exception(trans('front/privacy.export_pending'));
        }

        $request = GdprRequest::query()->create([
            'customer_id' => $customer->id,
            'type'        => GdprRequest::TYPE_EXPORT,
            'status'      => GdprRequest::STATUS_PENDING,
            'ip'          => $ip,
        ]);

        GdprExportJob::dispatch($request->id);

        activity('gdpr')
            ->performedOn($customer)
            ->withProperties(['request_id' => $request->id, 'type' => 'export'])
            ->log('客户申请 GDPR 数据导出');

        return $request;
    }

    /**
     * 提交账户删除（匿名化）申请。
     */
    public function requestDelete(Customer $customer, string $ip = ''): GdprRequest
    {
        if (GdprRequestRepo::getInstance()->findPendingDelete($customer->id)) {
            throw new Exception(trans('front/privacy.delete_pending'));
        }

        // 有未完成订单时不允许删除
        $openOrders = Order::query()
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', ['completed', 'cancelled', 'refunded'])
            ->exists();

        if ($openOrders) {
            throw new Exception(trans('front/privacy.delete_has_open_orders'));
        }

        $request = GdprRequest::query()->create([
            'customer_id' => $customer->id,
            'type'        => GdprRequest::TYPE_DELETE,
            'status'      => GdprRequest::STATUS_PENDING,
            'ip'          => $ip,
        ]);

        GdprAnonymizeJob::dispatch($request->id);

        activity('gdpr')
            ->performedOn($customer)
            ->withProperties(['request_id' => $request->id, 'type' => 'delete'])
            ->log('客户申请 GDPR 账户删除');

        return $request;
    }

    /**
     * 标记请求完成。
     */
    public function markCompleted(GdprRequest $request, string $filePath = ''): void
    {
        $request->status       = GdprRequest::STATUS_COMPLETED;
        $request->file_path    = $filePath;
        $request->completed_at = Carbon::now();
        $request->save();
    }

    /**
     * 标记请求失败。
     */
    public function markFailed(GdprRequest $request, string $message): void
    {
        $request->status        = GdprRequest::STATUS_FAILED;
        $request->error_message = $message;
        $request->completed_at  = Carbon::now();
        $request->save();
    }
}
