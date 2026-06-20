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
use NiceShoply\Common\Models\OptionValue;
use NiceShoply\Common\Repositories\OptionRepo;
use NiceShoply\Common\Repositories\OptionValueRepo;
use NiceShoply\Console\Requests\OptionValueRequest;

class OptionValueController extends BaseController
{
    protected $model = OptionValue::class;

    protected $repo = OptionValueRepo::class;

    /**
     * Display option values list page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        // 获取选项值数据
        $optionValues = OptionValueRepo::getInstance()->list($filters);

        // 获取所有选项组用于筛选下拉框
        $allOptionGroups = OptionRepo::getInstance()->all(['active' => 1]);

        $data = [
            'optionValues'    => $optionValues, // 选项值数据
            'allOptionGroups' => $allOptionGroups,
        ];

        return nice_view('console::option_values.index', $data);
    }

    /**
     * Store new option value
     *
     * @param  OptionValueRequest  $request
     * @return mixed
     * @throws Exception
     */
    public function store(OptionValueRequest $request): mixed
    {
        try {
            $data        = $request->all();
            $optionValue = OptionValueRepo::getInstance()->create($data);

            if ($request->ajax()) {
                return json_success(console_trans('common.created_success'), $optionValue);
            }

            return redirect(console_route('option_values.index'))
                ->with('instance', $optionValue)
                ->with('success', console_trans('common.created_success'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return json_fail($e->getMessage());
            }

            return redirect(console_route('option_values.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show option value details (for AJAX requests)
     *
     * @param  OptionValue  $optionValue
     * @return mixed
     * @throws Exception
     */
    public function show(OptionValue $optionValue): mixed
    {
        $data = [
            'id'        => $optionValue->id,
            'option_id' => $optionValue->option_id,
            'image'     => $optionValue->image,
            'position'  => $optionValue->position,
            'active'    => $optionValue->active,
            'name'      => $optionValue->name ?? [],
        ];

        return response()->json($data);
    }

    /**
     * Update existing option value
     *
     * @param  OptionValueRequest  $request
     * @param  OptionValue  $optionValue
     * @return mixed
     * @throws Exception
     */
    public function update(OptionValueRequest $request, OptionValue $optionValue): mixed
    {
        try {
            $data = $request->all();
            OptionValueRepo::getInstance()->update($optionValue, $data);

            if ($request->ajax()) {
                return json_success(console_trans('common.updated_success'), $optionValue);
            }

            return redirect(console_route('option_values.index'))
                ->with('instance', $optionValue)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return json_fail($e->getMessage());
            }

            return redirect(console_route('option_values.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete option value
     *
     * @param  OptionValue  $optionValue
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(OptionValue $optionValue): RedirectResponse
    {
        try {
            OptionValueRepo::getInstance()->destroy($optionValue);

            return redirect(console_route('option_values.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('option_values.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
