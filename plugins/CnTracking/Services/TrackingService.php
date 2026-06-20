<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnTracking\Services;

use Exception;
use Illuminate\Support\Facades\Http;

/**
 * 物流轨迹查询服务，支持快递鸟与快递100。
 */
class TrackingService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 查询轨迹。
     *
     * @param  string  $company  快递公司编码(如 SF、YTO、ZTO、JD)
     * @param  string  $number   运单号
     * @param  string  $phone    收/寄件人手机后四位(部分快递如顺丰必填)
     * @return array{success: bool, status: string, traces: array, raw: mixed}
     *
     * @throws Exception
     */
    public function query(string $company, string $number, string $phone = ''): array
    {
        $provider = (string) plugin_setting('cn_tracking', 'provider', 'kdniao');

        return match ($provider) {
            'kuaidi100' => $this->queryKuaidi100($company, $number, $phone),
            default     => $this->queryKdniao($company, $number),
        };
    }

    /**
     * 快递鸟即时查询(接口指令 1002)。
     *
     * @throws Exception
     */
    protected function queryKdniao(string $company, string $number): array
    {
        $ebusinessId = (string) plugin_setting('cn_tracking', 'kdniao_ebusiness_id');
        $apiKey      = (string) plugin_setting('cn_tracking', 'kdniao_api_key');
        if ($ebusinessId === '' || $apiKey === '') {
            throw new Exception('Kdniao credentials are not configured.');
        }

        $sandbox = ((int) plugin_setting('cn_tracking', 'sandbox', 0)) === 1;
        $url     = $sandbox
            ? 'http://sandboxapi.kdniao.com:8080/kdniaosandbox/gateway/exterfaceInvoke.json'
            : 'https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

        $requestData = json_encode([
            'ShipperCode' => strtoupper($company),
            'LogisticCode' => $number,
        ], JSON_UNESCAPED_UNICODE);

        // 快递鸟签名：base64(md5(RequestData + ApiKey))，再 urlencode
        $dataSign = urlencode(base64_encode(md5($requestData.$apiKey, false)));

        $response = Http::asForm()->timeout(15)->post($url, [
            'RequestData' => $requestData,
            'EBusinessID' => $ebusinessId,
            'RequestType' => '1002',
            'DataSign'    => $dataSign,
            'DataType'    => '2',
        ]);

        $json = $response->json();
        if (! is_array($json)) {
            throw new Exception('Kdniao response invalid.');
        }

        $traces = [];
        foreach (($json['Traces'] ?? []) as $t) {
            $traces[] = [
                'time'    => $t['AcceptTime'] ?? '',
                'content' => $t['AcceptStation'] ?? '',
            ];
        }

        return [
            'success' => (bool) ($json['Success'] ?? false),
            'status'  => $this->normalizeKdniaoState($json['State'] ?? ''),
            'traces'  => array_reverse($traces),
            'raw'     => $json,
        ];
    }

    /**
     * 快递100实时查询。
     *
     * @throws Exception
     */
    protected function queryKuaidi100(string $company, string $number, string $phone = ''): array
    {
        $customer = (string) plugin_setting('cn_tracking', 'kuaidi100_customer');
        $key      = (string) plugin_setting('cn_tracking', 'kuaidi100_key');
        if ($customer === '' || $key === '') {
            throw new Exception('Kuaidi100 credentials are not configured.');
        }

        $param = json_encode(array_filter([
            'com'   => strtolower($company),
            'num'   => $number,
            'phone' => $phone,
        ]), JSON_UNESCAPED_UNICODE);

        // 快递100签名：MD5(param + key + customer) 大写
        $sign = strtoupper(md5($param.$key.$customer));

        $response = Http::asForm()->timeout(15)->post('https://poll.kuaidi100.com/poll/query.do', [
            'customer' => $customer,
            'sign'     => $sign,
            'param'    => $param,
        ]);

        $json = $response->json();
        if (! is_array($json)) {
            throw new Exception('Kuaidi100 response invalid.');
        }

        $traces = [];
        foreach (($json['data'] ?? []) as $t) {
            $traces[] = [
                'time'    => $t['time'] ?? ($t['ftime'] ?? ''),
                'content' => $t['context'] ?? '',
            ];
        }

        return [
            'success' => ($json['status'] ?? '') === '200' || ($json['message'] ?? '') === 'ok',
            'status'  => $this->normalizeKuaidi100State((string) ($json['state'] ?? '')),
            'traces'  => $traces,
            'raw'     => $json,
        ];
    }

    protected function normalizeKdniaoState($state): string
    {
        return match ((string) $state) {
            '2'     => 'in_transit',
            '3'     => 'delivered',
            '4'     => 'problem',
            default => 'pending',
        };
    }

    protected function normalizeKuaidi100State(string $state): string
    {
        return match ($state) {
            '0'     => 'in_transit',
            '3'     => 'delivered',
            '5'     => 'delivered',
            '1', '6', '4' => 'problem',
            default => 'pending',
        };
    }
}
