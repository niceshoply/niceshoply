<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ReviewAftersale\Models\AftersaleRequest;
use Plugin\ReviewAftersale\Services\AftersaleService;

class AftersaleController extends BaseController
{
    protected string $modelClass = AftersaleRequest::class;

    public function index(Request $request): mixed
    {
        $requests = AftersaleRequest::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('ReviewAftersale::console.aftersales', compact('requests'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate([
                'status'       => 'required|in:pending,approved,rejected,processing,completed',
                'admin_remark' => 'nullable|string|max:500',
            ]);

            AftersaleService::getInstance()->changeStatus($id, $data['status'], $data['admin_remark'] ?? '');

            return json_success(__('ReviewAftersale::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
