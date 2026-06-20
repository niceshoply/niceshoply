<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GiftCard\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\GiftCard\Models\GiftCard;
use Plugin\GiftCard\Models\GiftCardBatch;
use Plugin\GiftCard\Services\GiftCardService;

class GiftCardController extends BaseController
{
    protected string $modelClass = GiftCardBatch::class;

    public function index(): mixed
    {
        $batches = GiftCardBatch::query()->withCount('cards')->orderByDesc('id')->paginate(20);

        return nice_view('GiftCard::console.index', compact('batches'));
    }

    public function generate(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'name'       => 'required|string|max:128',
                'face_value' => 'required|numeric|min:0.01',
                'quantity'   => 'required|integer|min:1|max:10000',
                'prefix'     => 'nullable|string|max:16',
                'expire_at'  => 'nullable|date',
            ]);

            $batch = GiftCardService::getInstance()->generateBatch(
                $data['name'],
                (float) $data['face_value'],
                (int) $data['quantity'],
                $data['prefix'] ?? '',
                $data['expire_at'] ?? null
            );

            return json_success(__('GiftCard::common.generated', ['count' => $batch->quantity]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function cards(Request $request, int $batchId): mixed
    {
        $batch = GiftCardBatch::query()->findOrFail($batchId);
        $cards = GiftCard::query()
            ->where('batch_id', $batchId)
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return nice_view('GiftCard::console.cards', compact('batch', 'cards'));
    }

    public function toggleCard(int $id): mixed
    {
        try {
            $card = GiftCard::query()->findOrFail($id);
            if ($card->status === 'used') {
                return json_fail(__('GiftCard::common.card_used'));
            }
            $card->status = $card->status === 'disabled' ? 'unused' : 'disabled';
            $card->save();

            return json_success(__('GiftCard::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
