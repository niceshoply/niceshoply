<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductQa\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ProductQa\Models\Answer;
use Plugin\ProductQa\Models\Question;
use Plugin\ProductQa\Services\ProductQAService;

class ProductQAController extends BaseController
{
    protected string $modelClass = Question::class;

    public function index(Request $request): mixed
    {
        $questions = Question::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('answers')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('ProductQa::console.index', compact('questions'));
    }

    public function auditQuestion(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate(['status' => 'required|in:approved,rejected,pending']);
            Question::query()->findOrFail($id)->update(['status' => $data['status']]);

            return json_success(__('ProductQa::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function toggleFeatured(int $id): mixed
    {
        try {
            $question = Question::query()->findOrFail($id);
            $question->is_featured = ! $question->is_featured;
            $question->save();

            return json_success(__('ProductQa::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function auditAnswer(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate(['status' => 'required|in:approved,rejected,pending']);
            Answer::query()->findOrFail($id)->update(['status' => $data['status']]);

            return json_success(__('ProductQa::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function merchantReply(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate(['content' => 'required|string|max:500']);
            ProductQAService::getInstance()->answer(0, $id, $data['content'], true);

            return json_success(__('ProductQa::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
