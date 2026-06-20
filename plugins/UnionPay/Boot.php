<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\UnionPay;

use Illuminate\Support\Facades\Log;
use Plugin\UnionPay\Services\UnionPayService;

class Boot
{
    public function init(): void
    {
        // 网页(PC)支付：把银联网关自动提交表单注入支付视图
        listen_hook_filter('service.payment.pay.union_pay.data', function (array $data) {
            try {
                $order   = $data['order'];
                $isWap   = request()->isMethod('get') && (bool) preg_match('/Mobile|Android|iPhone/i', (string) request()->userAgent());
                $service = UnionPayService::getInstance($order);
                $data['form_html'] = $isWap ? $service->wap() : $service->web();
            } catch (\Throwable $e) {
                $data['error'] = $e->getMessage();
                Log::channel('payment')->error('union_pay.web.failed', ['error' => $e->getMessage()]);
            }

            return $data;
        });

        // API 支付：返回自动提交表单 HTML 给前端容器渲染
        listen_hook_filter('service.payment.api.union_pay.data', function (array $data) {
            try {
                $order   = $data['order'];
                $scene   = request('scene', 'web');
                $service = UnionPayService::getInstance($order);
                $data['params'] = [
                    'form_html' => $scene === 'wap' ? $service->wap() : $service->web(),
                ];
            } catch (\Throwable $e) {
                $data['error'] = $e->getMessage();
            }

            return $data;
        });
    }
}
