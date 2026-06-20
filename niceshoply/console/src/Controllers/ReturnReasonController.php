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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\ReturnReason;
use NiceShoply\Common\Repositories\ReturnReasonRepo;
use NiceShoply\Console\Requests\ReturnReasonRequest;

class ReturnReasonController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => ReturnReasonRepo::getCriteria(),
            'reasons'  => ReturnReasonRepo::getInstance()->list($filters),
        ];

        return nice_view('console::return_reasons.index', $data);
    }

    /**
     * @return mixed
     */
    public function create(): mixed
    {
        $data = [
            'reason' => new ReturnReason,
        ];

        return nice_view('console::return_reasons.form', $data);
    }

    /**
     * @param  ReturnReasonRequest  $request
     * @return RedirectResponse
     */
    public function store(ReturnReasonRequest $request): RedirectResponse
    {
        try {
            ReturnReasonRepo::getInstance()->create($request->all());

            return redirect(console_route('return_reasons.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('return_reasons.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  ReturnReason  $returnReason
     * @return mixed
     */
    public function edit(ReturnReason $returnReason): mixed
    {
        $data = [
            'reason' => $returnReason,
        ];

        return nice_view('console::return_reasons.form', $data);
    }

    /**
     * @param  ReturnReasonRequest  $request
     * @param  ReturnReason  $returnReason
     * @return RedirectResponse
     */
    public function update(ReturnReasonRequest $request, ReturnReason $returnReason): RedirectResponse
    {
        try {
            ReturnReasonRepo::getInstance()->update($returnReason, $request->all());

            return redirect(console_route('return_reasons.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('return_reasons.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  ReturnReason  $returnReason
     * @return RedirectResponse
     */
    public function destroy(ReturnReason $returnReason): RedirectResponse
    {
        try {
            ReturnReasonRepo::getInstance()->destroy($returnReason);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return mixed
     */
    public function active(Request $request, int $id): mixed
    {
        $reason         = ReturnReason::query()->findOrFail($id);
        $reason->active = $request->get('status', ! $reason->active);
        $reason->save();

        return json_success(console_trans('common.updated_success'));
    }
}
