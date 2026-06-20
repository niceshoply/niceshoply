<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiImageStudio\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Plugin\AiImageStudio\Models\AiImage;

/**
 * 文生图服务，对接 OpenAI 兼容图像接口。
 */
class AiImageService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 生成图片，返回入库后的 AiImage 列表。
     *
     * @return AiImage[]
     *
     * @throws Exception
     */
    public function generate(string $prompt, int $count = 1, int $operatorId = 0): array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            throw new Exception(__('AiImageStudio::common.empty_prompt'));
        }

        $base  = rtrim((string) plugin_setting('ai_image_studio', 'base_url', ''), '/');
        $key   = (string) plugin_setting('ai_image_studio', 'api_key', '');
        $model = (string) plugin_setting('ai_image_studio', 'model', 'dall-e-3');
        $size  = (string) plugin_setting('ai_image_studio', 'size', '1024x1024');

        if ($base === '' || $key === '') {
            throw new Exception(__('AiImageStudio::common.no_credentials'));
        }

        $count = max(1, min($count, 4));

        $resp = Http::withToken($key)->timeout(120)->post("{$base}/images/generations", [
            'model'           => $model,
            'prompt'          => $prompt,
            'n'               => $count,
            'size'            => $size,
            'response_format' => 'b64_json',
        ]);

        if (! $resp->successful()) {
            throw new Exception('Image API error: '.$resp->status().' '.mb_substr($resp->body(), 0, 300));
        }

        $items = $resp->json('data') ?? [];
        if (empty($items)) {
            throw new Exception(__('AiImageStudio::common.no_result'));
        }

        $saved = [];
        foreach ($items as $item) {
            $binary = null;
            if (! empty($item['b64_json'])) {
                $binary = base64_decode($item['b64_json']);
            } elseif (! empty($item['url'])) {
                $binary = @file_get_contents($item['url']);
            }
            if (! $binary) {
                continue;
            }

            $relative = 'ai_images/'.date('Ym').'/'.uniqid('ai_', true).'.png';
            Storage::disk('public')->put($relative, $binary);

            $saved[] = AiImage::query()->create([
                'prompt'      => $prompt,
                'model'       => $model,
                'size'        => $size,
                'path'        => $relative,
                'operator_id' => $operatorId,
                'created_at'  => now(),
            ]);
        }

        if (empty($saved)) {
            throw new Exception(__('AiImageStudio::common.no_result'));
        }

        return $saved;
    }
}
