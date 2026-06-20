<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Bargain\Models\BargainTask;
use Plugin\Bargain\Services\BargainService;

class BargainController extends BaseController
{
    public function start(Request $request): mixed
    {
        try {
            $activityId = (int) $request->get('activity_id');
            $skuPrice   = (float) $request->get('sku_price', 0);
            $customerId = (int) token_customer_id();

            $task = BargainService::getInstance()->startTask($activityId, $customerId, $skuPrice);

            return json_success('ok', $this->taskData($task));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function cut(Request $request): mixed
    {
        try {
            $taskId   = (int) $request->get('task_id');
            $helperId = (int) token_customer_id();

            $result = BargainService::getInstance()->cut($taskId, $helperId);

            return json_success(__('Bargain::common.cut_success'), $result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function task(int $id): mixed
    {
        $task = BargainTask::query()->with('activity')->findOrFail($id);
        BargainService::getInstance()->refreshStatus($task);

        return json_success('ok', $this->taskData($task));
    }

    /**
     * 砍价成功后选择用该任务结算：写入 checkout.reference。
     */
    public function apply(Request $request): mixed
    {
        try {
            $taskId     = (int) $request->get('task_id');
            $customerId = (int) token_customer_id();

            $task = BargainTask::query()->findOrFail($taskId);
            if ($task->customer_id !== $customerId) {
                throw new Exception(__('Bargain::common.task_not_owner'));
            }
            if ($task->status !== 'done') {
                throw new Exception(__('Bargain::common.task_not_done'));
            }

            $checkout                          = CheckoutService::getInstance($customerId);
            $reference                         = $checkout->getCheckoutData()['reference'] ?? [];
            $reference['bargain_task_id']      = $taskId;
            $checkout->updateValues(['reference' => $reference]);

            return json_success(__('Bargain::common.apply_ready'), ['task_id' => $taskId]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function taskData(BargainTask $task): array
    {
        return [
            'task_id'              => $task->id,
            'activity_id'          => $task->activity_id,
            'status'               => $task->status,
            'origin_price'         => $task->origin_price,
            'floor_price'          => $task->floor_price,
            'current_price'        => $task->current_price,
            'current_price_format' => currency_format($task->current_price),
            'cut_total'            => round($task->origin_price - $task->current_price, 2),
            'expire_at'            => $task->expire_at?->toIso8601String(),
        ];
    }
}
