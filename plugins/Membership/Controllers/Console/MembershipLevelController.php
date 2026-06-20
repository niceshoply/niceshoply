<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Membership\Models\MembershipLevel;

class MembershipLevelController extends BaseController
{
    protected string $modelClass = MembershipLevel::class;

    public function index(): mixed
    {
        $levels = MembershipLevel::query()->orderBy('sort')->orderBy('min_spent')->paginate(20);

        return nice_view('Membership::console.index', compact('levels'));
    }

    public function create(): mixed
    {
        $level = new MembershipLevel(['active' => true]);

        return nice_view('Membership::console.form', compact('level'));
    }

    public function store(Request $request): mixed
    {
        try {
            MembershipLevel::query()->create($this->validateData($request));

            return json_success(__('Membership::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $level = MembershipLevel::query()->findOrFail($id);

        return nice_view('Membership::console.form', compact('level'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            MembershipLevel::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('Membership::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            MembershipLevel::query()->findOrFail($id)->delete();

            return json_success(__('Membership::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'             => 'required|string|max:64',
            'min_spent'        => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'sort'             => 'nullable|integer|min:0',
            'active'           => 'nullable|boolean',
        ]);
    }
}
