<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\PointsMall\Models\Redemption;

class RedemptionController extends BaseController
{
    protected string $modelClass = Redemption::class;

    public function index(Request $request): mixed
    {
        $redemptions = Redemption::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('PointsMall::console.redemptions', compact('redemptions'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate([
                'status' => 'required|in:pending,shipped,completed,cancelled',
                'remark' => 'nullable|string|max:500',
            ]);

            $redemption = Redemption::query()->findOrFail($id);
            $redemption->status = $data['status'];
            if (isset($data['remark'])) {
                $redemption->remark = $data['remark'];
            }
            $redemption->save();

            return json_success(__('PointsMall::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
