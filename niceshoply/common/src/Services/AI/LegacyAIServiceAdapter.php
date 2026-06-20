<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\AI;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 既有（国产）AI 适配器装饰器。
 *
 * 在混合 AI 架构中，标准 OpenAI 兼容通道由 {@see LaravelAIService} 承担，
 * 而 DeepSeek / Kimi / 豆包 / 千问 / 混元等既有适配器统一经本装饰器接入，
 * 在不改动既有实现的前提下补齐以下健壮性：
 *  - 流式优雅降级：底层 stream() 抛错或无产出时，自动回退为整段 generate()，
 *    保证前端 SSE 始终能拿到内容；
 *  - 统一异常日志，便于排查国产模型差异。
 */
class LegacyAIServiceAdapter implements AIServiceInterface
{
    public function __construct(
        private AIServiceInterface $inner,
        private string $key = 'legacy',
    ) {}

    /**
     * 透传一次性生成。
     *
     * @param  string  $prompt
     * @param  array  $options
     * @return string
     */
    public function generate(string $prompt, array $options = []): string
    {
        return $this->inner->generate($prompt, $options);
    }

    /**
     * 流式生成（带优雅降级）。
     *
     * @param  string  $prompt
     * @param  array  $options
     * @return iterable<string>
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        $yielded = false;

        try {
            foreach ($this->inner->stream($prompt, $options) as $chunk) {
                $yielded = true;
                yield $chunk;
            }
        } catch (Throwable $e) {
            Log::warning("Legacy AI [{$this->key}] 流式失败，回退整段生成：".$e->getMessage());

            // 已产出部分内容则不再回退，避免内容重复
            if ($yielded) {
                return;
            }
        }

        // 底层无流式产出（不支持或为空）→ 回退为整段生成
        if (! $yielded) {
            yield $this->inner->generate($prompt, $options);
        }
    }

    /**
     * 透传配置校验。
     *
     * @param  array  $config
     * @return bool
     */
    public function validateConfig(array $config): bool
    {
        return $this->inner->validateConfig($config);
    }

    /**
     * 被装饰的底层服务实例。
     *
     * @return AIServiceInterface
     */
    public function getInner(): AIServiceInterface
    {
        return $this->inner;
    }

    /**
     * 模型信息（装饰器层通用描述，具体能力以底层为准）。
     *
     * @return array
     */
    public static function getModelInfo(): array
    {
        return [
            'name'               => 'Legacy AI Adapter',
            'supports_streaming' => true,
            'source'             => 'legacy_adapter',
        ];
    }
}
