<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductQa\Services;

use Plugin\ProductQa\Models\Answer;
use Plugin\ProductQa\Models\Question;

class ProductQAService
{
    public static function getInstance(): static
    {
        return new static;
    }

    protected function initialStatus(): string
    {
        return (bool) plugin_setting('product_qa', 'need_audit', true) ? 'pending' : 'approved';
    }

    public function ask(int $customerId, int $productId, string $content): Question
    {
        return Question::query()->create([
            'product_id'  => $productId,
            'customer_id' => $customerId,
            'content'     => $content,
            'status'      => $this->initialStatus(),
        ]);
    }

    public function answer(int $customerId, int $questionId, string $content, bool $isMerchant = false): Answer
    {
        $question = Question::query()->findOrFail($questionId);

        $answer = Answer::query()->create([
            'question_id' => $question->id,
            'customer_id' => $customerId,
            'is_merchant' => $isMerchant,
            'content'     => $content,
            'status'      => $isMerchant ? 'approved' : $this->initialStatus(),
        ]);

        if ($answer->status === 'approved') {
            $question->increment('answer_count');
        }

        return $answer;
    }

    /**
     * 商详页已通过问答（含已通过回答）。
     */
    public function listForProduct(int $productId, int $perPage = 10)
    {
        return Question::query()
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->with(['answers' => fn ($q) => $q->where('status', 'approved')->orderByDesc('is_merchant')->orderBy('id')])
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
