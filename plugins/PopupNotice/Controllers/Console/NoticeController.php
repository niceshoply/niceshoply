<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PopupNotice\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\PopupNotice\Models\SiteNotice;

class NoticeController extends BaseController
{
    protected string $modelClass = SiteNotice::class;

    public function index(): mixed
    {
        $notices = SiteNotice::query()->orderBy('sort')->orderByDesc('id')->paginate(20);

        return nice_view('PopupNotice::console.index', compact('notices'));
    }

    public function create(): mixed
    {
        $notice = new SiteNotice(['type' => 'popup', 'scope' => 'all', 'is_active' => true]);

        return nice_view('PopupNotice::console.form', compact('notice'));
    }

    public function store(Request $request): mixed
    {
        try {
            SiteNotice::query()->create($this->validateData($request));

            return json_success(__('PopupNotice::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function edit(int $id): mixed
    {
        $notice = SiteNotice::query()->findOrFail($id);

        return nice_view('PopupNotice::console.form', compact('notice'));
    }

    public function update(Request $request, int $id): mixed
    {
        try {
            SiteNotice::query()->findOrFail($id)->update($this->validateData($request));

            return json_success(__('PopupNotice::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            SiteNotice::query()->findOrFail($id)->delete();

            return json_success(__('PopupNotice::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'     => 'required|string|max:191',
            'type'      => 'required|in:popup,bar',
            'content'   => 'nullable|string|max:2000',
            'image'     => 'nullable|string|max:500',
            'link_url'  => 'nullable|string|max:500',
            'scope'     => 'required|in:all,home',
            'start_at'  => 'nullable|date',
            'end_at'    => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'nullable|boolean',
            'sort'      => 'nullable|integer|min:0',
        ]);
    }
}
