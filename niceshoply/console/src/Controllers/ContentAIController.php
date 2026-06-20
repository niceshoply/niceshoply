<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NiceShoply\Common\Jobs\GenerateAIContentJob;
use NiceShoply\Common\Services\AI\AIServiceManager;

class ContentAIController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function generate(Request $request): mixed
    {
        try {
            $allParams = $request->all();
            Log::info('AI Generate Request: '.json_encode($allParams));

            $purpose = $request->get('purpose', 'general');
            $prompt  = $request->get('prompt', '');
            $options = $request->get('options', []);

            if (empty($prompt) && $request->has('value')) {
                $prompt = $request->get('value');
            }

            if ($request->has('column')) {
                $options['column'] = $request->get('column');
            }
            if ($request->has('lang')) {
                $options['lang'] = $request->get('lang');
            }

            if (empty($prompt)) {
                throw new Exception('Empty prompt');
            }

            // When queue is async, dispatch job and return task ID for polling
            if (config('queue.default') !== 'sync') {
                $taskId = Str::uuid()->toString();
                Cache::put(GenerateAIContentJob::cacheKey($taskId), ['status' => 'pending'], 600);
                GenerateAIContentJob::dispatch($taskId, $prompt, $purpose, $options);

                return read_json_success(['task_id' => $taskId]);
            }

            // Sync mode: generate directly
            $manager = AIServiceManager::getInstance();
            $result  = $manager->generate($prompt, $purpose, $options);

            $data = [
                'message' => $result,
                'model'   => $manager->getModelForPurpose($purpose),
            ];

            Log::info('AI Generate Success: '.json_encode($data));

            return read_json_success($data);
        } catch (Exception $e) {
            Log::error('AI Generate Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return json_fail($e->getMessage());
        }
    }

    /**
     * AI 流式生成（SSE）。
     *
     * 以 text/event-stream 持续推送增量片段，前端可用 EventSource / fetch reader 实时渲染。
     * 每个片段格式：`data: {"content":"..."}`；结束推送 `data: [DONE]`；异常推送 `data: {"error":"..."}`。
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function stream(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $purpose = $request->get('purpose', 'general');
        $prompt  = (string) $request->get('prompt', $request->get('value', ''));
        $options = (array) $request->get('options', []);

        if ($request->has('column')) {
            $options['column'] = $request->get('column');
        }
        if ($request->has('lang')) {
            $options['lang'] = $request->get('lang');
        }

        return response()->stream(function () use ($prompt, $purpose, $options) {
            $send = function (array $payload): void {
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
            };

            if (trim($prompt) === '') {
                $send(['error' => 'Empty prompt']);

                return;
            }

            try {
                $manager = AIServiceManager::getInstance();
                foreach ($manager->stream($prompt, $purpose, $options) as $chunk) {
                    if ($chunk !== '') {
                        $send(['content' => $chunk]);
                    }
                }
                $send(['done' => true]);
                echo "data: [DONE]\n\n";
                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
            } catch (Exception $e) {
                Log::error('AI Stream Error: '.$e->getMessage());
                $send(['error' => $e->getMessage()]);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=utf-8',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Poll AI generation task status
     *
     * @param  Request  $request
     * @return mixed
     */
    public function status(Request $request): mixed
    {
        $taskId = $request->get('task_id', '');

        if (empty($taskId)) {
            return json_fail('Missing task_id');
        }

        $result = Cache::get(GenerateAIContentJob::cacheKey($taskId));

        if (! $result) {
            return json_fail('Task not found or expired');
        }

        return read_json_success($result);
    }

    /**
     * Get available AI model list
     *
     * @return mixed
     */
    public function getModels(): mixed
    {
        try {
            $manager = AIServiceManager::getInstance();
            $models  = $manager->getModelsForSelect();

            return read_json_success([
                'models' => $models,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Test model configuration
     *
     * @param  Request  $request
     * @return mixed
     */
    public function testModel(Request $request): mixed
    {
        try {
            $model  = $request->get('model');
            $config = $request->get('config', []);

            if (empty($model)) {
                throw new Exception('Empty model name');
            }

            $manager = AIServiceManager::getInstance();
            $isValid = $manager->validateModelConfig($model, $config);

            return read_json_success([
                'valid' => $isValid,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
