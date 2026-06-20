<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SmsMarketing\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\SmsMarketing\Models\SmsCampaign;
use Plugin\SmsMarketing\Models\SmsUnsubscribe;
use Plugin\SmsMarketing\Services\SmsMarketingService;

class SmsMarketingController extends BaseController
{
    protected string $modelClass = SmsCampaign::class;

    public function index(): mixed
    {
        $campaigns   = SmsCampaign::query()->orderByDesc('id')->get();
        $unsubCount  = SmsUnsubscribe::query()->count();
        $recipientCount = count(SmsMarketingService::getInstance()->recipients());

        return nice_view('SmsMarketing::console.index', compact('campaigns', 'unsubCount', 'recipientCount'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'            => 'nullable|integer',
                'name'          => 'required|string|max:100',
                'template_id'   => 'required|string|max:64',
                'template_data' => 'nullable|string',
            ]);

            $vars = [];
            if (! empty($data['template_data'])) {
                $decoded = json_decode($data['template_data'], true);
                if (is_array($decoded)) {
                    $vars = $decoded;
                }
            }

            SmsCampaign::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'          => $data['name'],
                    'template_id'   => $data['template_id'],
                    'template_data' => $vars,
                    'target'        => 'customers',
                    'status'        => SmsCampaign::STATUS_DRAFT,
                ]
            );

            return json_success(__('SmsMarketing::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function send(int $id): mixed
    {
        try {
            $campaign = SmsCampaign::query()->findOrFail($id);
            $r = SmsMarketingService::getInstance()->sendCampaign($campaign);

            return json_success(__('SmsMarketing::common.sent', ['sent' => $r['sent'], 'fail' => $r['fail']]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        SmsCampaign::query()->whereKey($id)->delete();

        return json_success(__('SmsMarketing::common.deleted'));
    }
}
