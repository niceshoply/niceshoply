<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Ewaybill\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Order;
use Plugin\Ewaybill\Models\EWaybill;

/**
 * 电子面单服务（快递鸟电子面单接口 RequestType=1007）。
 */
class EWaybillService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 为订单申请电子面单。
     *
     * @throws Exception
     */
    public function create(int $orderId, string $shipperCode): EWaybill
    {
        /** @var Order $order */
        $order = Order::query()->findOrFail($orderId);
        $shipperCode = strtoupper(trim($shipperCode));
        if ($shipperCode === '') {
            throw new Exception(__('Ewaybill::common.need_shipper'));
        }

        $ebusinessId = (string) plugin_setting('ewaybill', 'kdniao_ebusiness_id');
        $apiKey      = (string) plugin_setting('ewaybill', 'kdniao_api_key');
        if ($ebusinessId === '' || $apiKey === '') {
            throw new Exception(__('Ewaybill::common.no_credentials'));
        }

        $requestData = json_encode($this->buildRequest($order, $shipperCode), JSON_UNESCAPED_UNICODE);
        $dataSign    = urlencode(base64_encode(md5($requestData.$apiKey, false)));

        $sandbox = ((int) plugin_setting('ewaybill', 'sandbox', 0)) === 1;
        $url     = $sandbox
            ? 'http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json'
            : 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

        $response = Http::asForm()->timeout(20)->post($url, [
            'RequestData' => $requestData,
            'EBusinessID' => $ebusinessId,
            'RequestType' => '1007',
            'DataSign'    => $dataSign,
            'DataType'    => '2',
        ]);

        $json = $response->json();
        if (! is_array($json)) {
            throw new Exception(__('Ewaybill::common.invalid_response'));
        }

        $success    = (bool) ($json['Success'] ?? false);
        $orderEntity = $json['Order'] ?? [];

        return EWaybill::query()->create([
            'order_id'          => $order->id,
            'order_number'      => $order->number,
            'shipper_code'      => $shipperCode,
            'logistic_code'     => $orderEntity['LogisticCode'] ?? null,
            'kdniao_order_code' => $orderEntity['OrderCode'] ?? $order->number,
            'status'            => $success ? 'success' : 'failed',
            'message'           => $json['Reason'] ?? ($success ? 'ok' : 'failed'),
            'raw'               => $json,
            'print_data'        => [
                'PrintTemplate' => $json['PrintTemplate'] ?? null,
                'EBillID'       => $orderEntity['EBillID'] ?? null,
                'LogisticCode'  => $orderEntity['LogisticCode'] ?? null,
            ],
        ]);
    }

    protected function buildRequest(Order $order, string $shipperCode): array
    {
        return [
            'ShipperCode' => $shipperCode,
            'OrderCode'   => $order->number,
            'PayType'     => 1,  // 1-现付 2-到付 3-月结
            'ExpType'     => 1,  // 1-标准快件
            'Sender'      => [
                'Name'         => (string) plugin_setting('ewaybill', 'sender_name'),
                'Mobile'       => (string) plugin_setting('ewaybill', 'sender_mobile'),
                'ProvinceName' => (string) plugin_setting('ewaybill', 'sender_province'),
                'CityName'     => (string) plugin_setting('ewaybill', 'sender_city'),
                'ExpAreaName'  => (string) plugin_setting('ewaybill', 'sender_area'),
                'Address'      => (string) plugin_setting('ewaybill', 'sender_address'),
            ],
            'Receiver'    => [
                'Name'         => (string) $order->shipping_customer_name,
                'Mobile'       => (string) $order->shipping_telephone,
                'ProvinceName' => (string) $order->shipping_state,
                'CityName'     => (string) $order->shipping_city,
                'ExpAreaName'  => '',
                'Address'      => trim($order->shipping_address_1.' '.$order->shipping_address_2),
            ],
            'Commodity'   => [
                ['GoodsName' => 'Order '.$order->number],
            ],
            'Quantity'    => 1,
            'IsReturnPrintTemplate' => 1,
            'Remark'      => (string) $order->comment,
        ];
    }
}
