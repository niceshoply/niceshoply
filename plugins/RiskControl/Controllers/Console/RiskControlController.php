<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\RiskControl\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\RiskControl\Models\Blacklist;
use Plugin\RiskControl\Models\RiskEvent;

class RiskControlController extends BaseController
{
    protected string $modelClass = RiskEvent::class;

    public function events(Request $request): mixed
    {
        $query = RiskEvent::query()->orderByDesc('id');
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }
        if ($scene = $request->input('scene')) {
            $query->where('scene', $scene);
        }
        $events = $query->paginate(40)->withQueryString();

        return nice_view('RiskControl::console.events', compact('events'));
    }

    public function blacklist(): mixed
    {
        $list = Blacklist::query()->orderByDesc('id')->paginate(40);

        return nice_view('RiskControl::console.blacklist', compact('list'));
    }

    public function storeBlacklist(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'type'   => 'required|in:ip,email,phone',
                'value'  => 'required|string|max:191',
                'reason' => 'nullable|string|max:191',
            ]);

            Blacklist::query()->updateOrCreate(
                ['type' => $data['type'], 'value' => $data['value']],
                ['reason' => $data['reason'] ?? null]
            );

            return json_success(__('RiskControl::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroyBlacklist(int $id): mixed
    {
        Blacklist::query()->whereKey($id)->delete();

        return json_success(__('RiskControl::common.deleted'));
    }
}
