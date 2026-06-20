<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CnTracking\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\CnTracking\Services\TrackingService;

class TrackingController extends BaseController
{
    public function query(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'company' => 'required|string|max:32',
                'number'  => 'required|string|max:64',
                'phone'   => 'nullable|string|max:16',
            ]);

            $result = TrackingService::getInstance()->query(
                $data['company'],
                $data['number'],
                $data['phone'] ?? ''
            );

            return json_success('ok', $result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
