<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Customer\Withdrawal;
use NiceShoply\Common\Repositories\Customer\WithdrawalRepo;

class CustomerWithdrawalController extends BaseController
{
    /**
     * Display a listing of customer withdrawals.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria'    => WithdrawalRepo::getCriteria(),
            'withdrawals' => WithdrawalRepo::getInstance()->list($filters),
        ];

        return nice_view('console::withdrawals.index', $data);
    }

    /**
     * Display the specified withdrawal.
     *
     * @param  Withdrawal  $withdrawal
     * @return mixed
     */
    public function show(Withdrawal $withdrawal): mixed
    {
        $data = [
            'withdrawal' => $withdrawal,
        ];

        return nice_view('console::withdrawals.show', $data);
    }

    /**
     * Change withdrawal status.
     *
     * @param  Request  $request
     * @param  Withdrawal  $withdrawal
     * @return JsonResponse|RedirectResponse
     */
    public function changeStatus(Request $request, Withdrawal $withdrawal): JsonResponse|RedirectResponse
    {
        try {
            $status       = $request->get('status');
            $adminComment = $request->get('admin_comment', '');

            if (! in_array($status, Withdrawal::STATUSES)) {
                throw new Exception('Invalid status');
            }

            $data = [
                'status'        => $status,
                'admin_comment' => $adminComment,
            ];

            WithdrawalRepo::getInstance()->update($withdrawal, $data);

            $message = match ($status) {
                'approved' => console_trans('withdrawal.approved_success'),
                'rejected' => console_trans('withdrawal.rejected_success'),
                'paid'     => console_trans('withdrawal.paid_success'),
                default    => console_trans('common.updated_success'),
            };

            if ($request->expectsJson()) {
                return json_success($message, $withdrawal);
            }

            return redirect(console_route('withdrawals.show', $withdrawal->id))
                ->with('success', $message);

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return json_fail($e->getMessage());
            }

            return redirect(console_route('withdrawals.show', $withdrawal->id))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
