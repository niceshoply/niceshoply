<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Services\AI\AIServiceManager;

class GenerateAIContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        private readonly string $taskId,
        private readonly string $prompt,
        private readonly ?string $purpose,
        private readonly array $options
    ) {}

    public function handle(): void
    {
        $cacheKey = self::cacheKey($this->taskId);

        try {
            $manager = AIServiceManager::getInstance();
            $result  = $manager->generate($this->prompt, $this->purpose, $this->options);

            Cache::put($cacheKey, [
                'status'  => 'completed',
                'message' => $result,
                'model'   => $manager->getModelForPurpose($this->purpose),
            ], 600);

            Log::info("AI content generation completed for task {$this->taskId}");
        } catch (Exception $e) {
            Cache::put($cacheKey, [
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 600);

            Log::error("AI content generation failed for task {$this->taskId}: {$e->getMessage()}");
        }
    }

    public static function cacheKey(string $taskId): string
    {
        return "ai_task:{$taskId}";
    }
}
