<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\PointLogRepo;
use NiceShoply\Common\Services\Member\PointService;

/**
 * 积分流水后台控制器。
 */
class PointLogController extends BaseController
{
    public function index(Request $request): mixed
    {
        $data = [
            'criteria' => PointLogRepo::getCriteria(),
            'logs'     => PointLogRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::point_logs.index', $data);
    }

    /**
     * 后台手动调整客户积分。
     */
    public function adjust(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => 'required|integer|min:1',
            'points'      => 'required|integer|not_in:0',
            'comment'     => 'nullable|string|max:255',
        ]);

        try {
            PointService::getInstance()->adjust(
                (int) $request->input('customer_id'),
                (int) $request->input('points'),
                (string) $request->input('comment', '')
            );

            return back()->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
