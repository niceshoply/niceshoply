<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MarketingFlow\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\MarketingFlow\Models\Flow;
use Plugin\MarketingFlow\Models\FlowJob;
use Plugin\MarketingFlow\Services\MarketingFlowService;

class MarketingFlowController extends BaseController
{
    protected string $modelClass = Flow::class;

    public function index(): mixed
    {
        $flows   = Flow::query()->orderByDesc('id')->get();
        $pending = FlowJob::query()->where('status', 'pending')->count();
        $sent    = FlowJob::query()->where('status', 'sent')->count();
        $events  = MarketingFlowService::EVENTS;

        return nice_view('MarketingFlow::console.index', compact('flows', 'pending', 'sent', 'events'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'            => 'nullable|integer',
                'name'          => 'required|string|max:100',
                'trigger_event' => 'required|in:'.implode(',', MarketingFlowService::EVENTS),
                'delay_minutes' => 'nullable|integer|min:0',
                'title'         => 'required|string|max:191',
                'content'       => 'nullable|string',
                'is_active'     => 'nullable|boolean',
            ]);

            Flow::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'          => $data['name'],
                    'trigger_event' => $data['trigger_event'],
                    'delay_minutes' => (int) ($data['delay_minutes'] ?? 0),
                    'title'         => $data['title'],
                    'content'       => $data['content'] ?? '',
                    'is_active'     => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('MarketingFlow::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        Flow::query()->whereKey($id)->delete();

        return json_success(__('MarketingFlow::common.deleted'));
    }

    public function run(): mixed
    {
        $r = MarketingFlowService::getInstance()->runDue();

        return json_success(__('MarketingFlow::common.ran', ['sent' => $r['sent'], 'failed' => $r['failed']]));
    }
}
