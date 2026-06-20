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
use NiceShoply\Common\Models\State;
use NiceShoply\Common\Repositories\StateRepo;
use NiceShoply\Console\Requests\StateRequest;
use Throwable;

class StateController extends BaseController
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
            'criteria' => StateRepo::getCriteria(),
            'states'   => StateRepo::getInstance()->list($filters),
        ];

        return nice_view('console::states.index', $data);
    }

    /**
     * @param  State  $state
     * @return State
     */
    public function show(State $state)
    {
        return $state;
    }

    /**
     * State creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new State);
    }

    /**
     * @param  StateRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StateRequest $request): RedirectResponse
    {
        try {
            $data  = $request->all();
            $state = StateRepo::getInstance()->create($data);

            return redirect(console_route('states.index'))
                ->with('instance', $state)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('states.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  State  $state
     * @return mixed
     * @throws Exception
     */
    public function edit(State $state): mixed
    {
        return $this->form($state);
    }

    /**
     * @param  State  $state
     * @return mixed
     */
    public function form(State $state): mixed
    {
        $data = [
            'state' => $state,
        ];

        return nice_view('console::states.form', $data);
    }

    /**
     * @param  StateRequest  $request
     * @param  State  $state
     * @return mixed
     */
    public function update(StateRequest $request, State $state): mixed
    {
        try {
            $data = $request->all();
            StateRepo::getInstance()->update($state, $data);

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  State  $state
     * @return RedirectResponse
     */
    public function destroy(State $state): RedirectResponse
    {
        try {
            StateRepo::getInstance()->destroy($state);

            return redirect(console_route('states.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('states.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
