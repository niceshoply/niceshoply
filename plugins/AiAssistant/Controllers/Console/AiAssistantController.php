<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiAssistant\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\AiAssistant\Models\Conversation;
use Plugin\AiAssistant\Models\KbEntry;

class AiAssistantController extends BaseController
{
    protected string $modelClass = KbEntry::class;

    public function kb(): mixed
    {
        $entries = KbEntry::query()->orderBy('sort')->orderByDesc('id')->paginate(50);

        return nice_view('AiAssistant::console.kb', compact('entries'));
    }

    public function storeKb(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'      => 'nullable|integer',
                'title'   => 'required|string|max:191',
                'content' => 'required|string',
                'sort'    => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            KbEntry::query()->updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'title'     => $data['title'],
                    'content'   => $data['content'],
                    'sort'      => $data['sort'] ?? 0,
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            return json_success(__('AiAssistant::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroyKb(int $id): mixed
    {
        KbEntry::query()->whereKey($id)->delete();

        return json_success(__('AiAssistant::common.deleted'));
    }

    public function conversations(): mixed
    {
        $logs = Conversation::query()->orderByDesc('id')->paginate(40);

        return nice_view('AiAssistant::console.conversations', compact('logs'));
    }
}
