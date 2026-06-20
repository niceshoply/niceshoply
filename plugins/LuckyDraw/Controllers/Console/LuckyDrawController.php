<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LuckyDraw\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\LuckyDraw\Models\DrawRecord;
use Plugin\LuckyDraw\Models\Prize;

class LuckyDrawController extends BaseController
{
    protected string $modelClass = Prize::class;

    public function prizes(): mixed
    {
        $prizes = Prize::query()->orderBy('sort')->orderByDesc('id')->get();

        return nice_view('LuckyDraw::console.prizes', compact('prizes'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'        => 'nullable|integer',
                'name'      => 'required|string|max:100',
                'type'      => 'required|in:thanks,points,coupon',
                'value'     => 'nullable|string|max:64',
                'weight'    => 'required|integer|min:0',
                'stock'     => 'required|integer|min:-1',
                'sort'      => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            Prize::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name'      => $data['name'],
                    'type'      => $data['type'],
                    'value'     => $data['value'] ?? '',
                    'weight'    => (int) $data['weight'],
                    'stock'     => (int) $data['stock'],
                    'sort'      => (int) ($data['sort'] ?? 0),
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('LuckyDraw::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        Prize::query()->whereKey($id)->delete();

        return json_success(__('LuckyDraw::common.deleted'));
    }

    public function records(): mixed
    {
        $records = DrawRecord::query()->orderByDesc('id')->paginate(40);

        return nice_view('LuckyDraw::console.records', compact('records'));
    }
}
