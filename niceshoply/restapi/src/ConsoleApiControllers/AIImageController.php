<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Services\AI\AIServiceManager;

/**
 * 后台 AI 图片生成 API 控制器（IMP-17）
 */
class AIImageController extends BaseController
{
    /**
     * 当前图片模型信息。
     *
     * @return mixed
     */
    public function modelsInfo(): mixed
    {
        $model = system_setting('ai_image_model') ?: system_setting('ai_model', 'openai');

        return read_json_success(['image_model' => $model]);
    }

    /**
     * 根据文本提示生成图片。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function generate(Request $request): mixed
    {
        set_time_limit(300);

        $request->validate([
            'prompt' => 'required|string|max:4000',
        ]);

        try {
            $options = array_filter([
                'size'      => $request->input('size'),
                'quality'   => $request->input('quality'),
                'model'     => $request->input('model'),
                'save_path' => $request->input('save_path', 'ai-images'),
            ], fn ($v) => $v !== null && $v !== '');

            $result = AIServiceManager::getInstance()->generateImage($request->input('prompt'), $options);

            Log::info('AI 图片生成成功', ['path' => $result['path']]);

            return create_json_success($result);
        } catch (Exception $e) {
            Log::error('AI 图片生成失败: '.$e->getMessage());

            return json_fail($e->getMessage());
        }
    }
}
