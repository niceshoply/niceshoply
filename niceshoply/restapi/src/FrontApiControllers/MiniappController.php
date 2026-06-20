<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\JwtTokenService;
use NiceShoply\RestAPI\Libraries\MiniApp\Auth;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;
use Throwable;

class MiniappController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws HttpClientException\DecodingExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\TransportExceptionInterface
     * @throws Throwable
     */
    public function index(Request $request): mixed
    {
        try {
            $code = $request->get('code');
            if (empty($code)) {
                throw new Exception('Empty MiniApp Code');
            }

            $miniAppAuth = Auth::getInstance($code);
            $customer    = $miniAppAuth->findOrCreateCustomerByCode();

            $jwtService = app(JwtTokenService::class);
            $tokenData  = $jwtService->issueToken($customer, 'customer_api', 'miniapp');

            return create_json_success($tokenData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
