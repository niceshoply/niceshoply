<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Subscription\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Subscription\Models\Subscription;
use Plugin\Subscription\Services\SubscriptionService;

class SubscriptionController extends BaseController
{
    public function index(): mixed
    {
        $customerId = (int) token_customer_id();
        if ($customerId <= 0) {
            return json_fail(__('Subscription::common.need_login'));
        }

        return json_success('ok', SubscriptionService::getInstance()->listForCustomer($customerId));
    }

    public function store(Request $request): mixed
    {
        try {
            $customerId = (int) token_customer_id();
            $sub = SubscriptionService::getInstance()->subscribe($customerId, $request->all());

            return json_success(__('Subscription::common.subscribed'), $sub);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function pause(int $id): mixed
    {
        return $this->changeStatus($id, Subscription::STATUS_PAUSED, 'paused');
    }

    public function resume(int $id): mixed
    {
        return $this->changeStatus($id, Subscription::STATUS_ACTIVE, 'resumed');
    }

    public function cancel(int $id): mixed
    {
        return $this->changeStatus($id, Subscription::STATUS_CANCELLED, 'cancelled');
    }

    private function changeStatus(int $id, string $status, string $msgKey): mixed
    {
        try {
            $customerId = (int) token_customer_id();
            $sub = SubscriptionService::getInstance()->setStatus($customerId, $id, $status);

            return json_success(__('Subscription::common.'.$msgKey), $sub);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
