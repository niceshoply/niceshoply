<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiImageStudio\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\AiImageStudio\Models\AiImage;
use Plugin\AiImageStudio\Services\AiImageService;

class AiImageStudioController extends BaseController
{
    protected string $modelClass = AiImage::class;

    public function index(): mixed
    {
        $images = AiImage::query()->orderByDesc('id')->paginate(24);

        return nice_view('AiImageStudio::console.index', compact('images'));
    }

    public function generate(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'prompt' => 'required|string|max:1000',
                'count'  => 'nullable|integer|min:1|max:4',
            ]);

            $operatorId = (int) (auth()->id() ?? 0);
            $images = AiImageService::getInstance()->generate(
                $data['prompt'],
                (int) ($data['count'] ?? 1),
                $operatorId
            );

            $payload = array_map(fn ($img) => ['id' => $img->id, 'url' => $img->url], $images);

            return json_success(__('AiImageStudio::common.generated', ['count' => count($payload)]), $payload);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroy(int $id): mixed
    {
        $img = AiImage::query()->find($id);
        if ($img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        return json_success(__('AiImageStudio::common.deleted'));
    }
}
