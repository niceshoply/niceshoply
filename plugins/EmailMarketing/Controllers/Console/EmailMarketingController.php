<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\EmailMarketing\Models\EmailCampaign;
use Plugin\EmailMarketing\Models\EmailSubscriber;
use Plugin\EmailMarketing\Services\EmailMarketingService;

class EmailMarketingController extends BaseController
{
    protected string $modelClass = EmailCampaign::class;

    public function campaigns(): mixed
    {
        $campaigns = EmailCampaign::query()->orderByDesc('id')->paginate(20);

        return nice_view('EmailMarketing::console.campaigns', compact('campaigns'));
    }

    public function storeCampaign(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'           => 'nullable|integer',
                'subject'      => 'required|string|max:255',
                'body'         => 'required|string',
                'target'       => 'required|in:subscribers,customers',
                'scheduled_at' => 'nullable|date',
            ]);

            $id = (int) ($data['id'] ?? 0);
            unset($data['id']);

            if ($id > 0) {
                $campaign = EmailCampaign::query()->findOrFail($id);
                if ($campaign->status === EmailCampaign::STATUS_SENT) {
                    return json_fail(__('EmailMarketing::common.already_sent'));
                }
                $campaign->update($data);
            } else {
                EmailCampaign::query()->create($data);
            }

            return json_success(__('EmailMarketing::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function send(int $id): mixed
    {
        try {
            $campaign = EmailCampaign::query()->findOrFail($id);
            $result = EmailMarketingService::getInstance()->sendCampaign($campaign);

            return json_success(__('EmailMarketing::common.send_done', $result));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroyCampaign(int $id): mixed
    {
        try {
            EmailCampaign::query()->findOrFail($id)->delete();

            return json_success(__('EmailMarketing::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function subscribers(Request $request): mixed
    {
        $subscribers = EmailSubscriber::query()->orderByDesc('id')->paginate(50);
        $total       = EmailSubscriber::query()->where('subscribed', true)->count();

        return nice_view('EmailMarketing::console.subscribers', compact('subscribers', 'total'));
    }
}
