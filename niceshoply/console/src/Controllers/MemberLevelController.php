<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\MemberLevel;
use NiceShoply\Common\Repositories\MemberLevelRepo;

/**
 * 会员等级后台控制器。
 */
class MemberLevelController extends BaseController
{
    public function index(Request $request): mixed
    {
        $data = [
            'criteria'     => MemberLevelRepo::getCriteria(),
            'memberLevels' => MemberLevelRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::member_levels.index', $data);
    }

    public function create(): mixed
    {
        return $this->form(new MemberLevel);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            MemberLevelRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('member_levels.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('member_levels.create'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(MemberLevel $memberLevel): mixed
    {
        return $this->form($memberLevel);
    }

    public function update(Request $request, MemberLevel $memberLevel): RedirectResponse
    {
        try {
            MemberLevelRepo::getInstance()->update($memberLevel, $this->normalize($request));

            return redirect(console_route('member_levels.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('member_levels.edit', [$memberLevel->id]))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(MemberLevel $memberLevel): RedirectResponse
    {
        try {
            MemberLevelRepo::getInstance()->destroy($memberLevel);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 切换启用状态。
     */
    public function active(Request $request, int $id): mixed
    {
        $memberLevel         = MemberLevel::query()->findOrFail($id);
        $memberLevel->active = ! $memberLevel->active;
        $memberLevel->save();

        return json_success(console_trans('common.updated_success'));
    }

    private function form(MemberLevel $memberLevel): mixed
    {
        return nice_view('console::member_levels.form', [
            'memberLevel'          => $memberLevel,
            'thresholdTypeOptions' => MemberLevelRepo::getThresholdTypeOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(Request $request): array
    {
        return [
            'name'             => $request->input('name', ''),
            'label'            => $request->input('label', ''),
            'description'      => $request->input('description', ''),
            'threshold_type'   => $request->input('threshold_type', 'amount'),
            'threshold_value'  => $request->input('threshold_value', 0),
            'discount_percent' => $request->input('discount_percent', 0),
            'free_shipping'    => (bool) $request->input('free_shipping', false),
            'priority'         => $request->input('priority', 0),
            'active'           => (bool) $request->input('active', true),
        ];
    }
}
