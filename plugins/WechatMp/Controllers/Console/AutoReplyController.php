<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatMp\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\WechatMp\Models\AutoReply;

class AutoReplyController extends BaseController
{
    protected string $modelClass = AutoReply::class;

    public function index(): mixed
    {
        $replies   = AutoReply::query()->orderBy('sort')->orderByDesc('id')->get();
        $serveUrl  = url('/wechat/oa/serve');

        return nice_view('WechatMp::console.index', compact('replies', 'serveUrl'));
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $this->validateData($request);
            $id   = (int) $request->input('id', 0);

            if ($id > 0) {
                AutoReply::query()->findOrFail($id)->update($data);
            } else {
                AutoReply::query()->create($data);
            }

            return json_success(__('WechatMp::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        try {
            AutoReply::query()->findOrFail($id)->delete();

            return json_success(__('WechatMp::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'match_type' => 'required|in:equal,contains,default',
            'keyword'    => 'nullable|string|max:128',
            'content'    => 'required|string',
            'is_active'  => 'nullable|boolean',
            'sort'       => 'nullable|integer|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
