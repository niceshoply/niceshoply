<?php
namespace Plugin\GlobalPay;

use Illuminate\Support\Facades\Log;
use Plugin\GlobalPay\Services\GlobalPayService;

class Boot
{
    public function init(): void
    {
        listen_hook_filter('service.payment.pay.global_pay.data', function (array $data) {
            try {
                $order = $data['order'];
                $url   = GlobalPayService::getInstance($order)->createRedirectUrl();
                $data['redirect_url'] = $url;
            } catch (\Throwable $e) {
                $data['error'] = $e->getMessage();
                Log::channel('payment')->error('global_pay.web.failed', ['error' => $e->getMessage()]);
            }

            return $data;
        });

        listen_hook_filter('service.payment.api.global_pay.data', function (array $data) {
            try {
                $order = $data['order'];
                $url   = GlobalPayService::getInstance($order)->createRedirectUrl();
                $data['params'] = ['redirect_url' => $url];
            } catch (\Throwable $e) {
                $data['error'] = $e->getMessage();
            }

            return $data;
        });
    }
}
