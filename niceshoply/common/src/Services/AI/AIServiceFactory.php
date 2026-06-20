<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\AI;

class AIServiceFactory
{
    private static array $services = [];

    private static array $modelMap = [
        'openai'     => OpenAIService::class,
        'laravel_ai' => LaravelAIService::class,
        'deepseek'   => DeepSeekService::class,
        'kimi'       => KimiService::class,
        'doubao'     => DoubaoService::class,
        'qianwen'    => QianwenService::class,
        'hunyuan'    => HunyuanService::class,
    ];

    /**
     * 走 LegacyAIServiceAdapter 装饰的既有（国产）模型。
     *
     * 这些模型经装饰器统一接入，获得流式优雅降级等健壮性，
     * 同时不影响 OpenAI（含图片能力）与 laravel_ai 标准通道。
     *
     * @var string[]
     */
    private static array $legacyAdapterModels = [
        'deepseek', 'kimi', 'doubao', 'qianwen', 'hunyuan',
    ];

    /**
     * Get AI service instance
     *
     * @param  string  $model  The model name
     * @param  array  $config  Configuration array for the service
     * @return AIServiceInterface The AI service instance
     * @throws \InvalidArgumentException When model is not supported or class not found
     */
    public static function make(string $model, array $config = []): AIServiceInterface
    {
        $cacheKey = $model.'_'.md5(serialize($config));

        if (isset(self::$services[$cacheKey])) {
            return self::$services[$cacheKey];
        }

        // 混合架构：优先解析运行期注册的自定义提供方（插件 / laravel-ai 桥接等）
        $registry = AIProviderRegistry::getInstance();
        if ($registry->has($model)) {
            $config  = apply_filters("ai.model_config.{$model}", $config);
            $service = $registry->resolve($model, $config);

            self::$services[$cacheKey] = $service;

            return $service;
        }

        // Allow extension of model mapping via hooks
        $modelMap = apply_filters('ai.available_models', self::$modelMap);

        if (! isset($modelMap[$model])) {
            throw new \InvalidArgumentException("Unsupported AI model: {$model}");
        }

        $serviceClass = $modelMap[$model];

        if (! class_exists($serviceClass)) {
            throw new \InvalidArgumentException("AI service class not found: {$serviceClass}");
        }

        // Allow modification of configuration via hooks
        $config = apply_filters("ai.model_config.{$model}", $config);

        $service = new $serviceClass($config);

        if (! $service instanceof AIServiceInterface) {
            throw new \InvalidArgumentException('Service class must implement AIServiceInterface');
        }

        // 国产既有模型统一经装饰器接入（流式优雅降级等健壮性增强）
        if (in_array($model, self::$legacyAdapterModels, true)) {
            $service = new LegacyAIServiceAdapter($service, $model);
        }

        self::$services[$cacheKey] = $service;

        return $service;
    }

    /**
     * Get all available models information
     *
     * @return array Array of available models with their information
     */
    public static function getAvailableModels(): array
    {
        $modelMap = apply_filters('ai.available_models', self::$modelMap);
        $models   = [];

        foreach ($modelMap as $key => $class) {
            if (class_exists($class) && method_exists($class, 'getModelInfo')) {
                try {
                    $models[$key] = $class::getModelInfo();
                } catch (\Exception $e) {
                    // Ignore services that cannot provide model info
                }
            }
        }

        // 合并运行期注册的自定义提供方元数据
        foreach (AIProviderRegistry::getInstance()->all() as $key => $meta) {
            $models[$key] = array_merge(['name' => $meta['name'] ?? $key], $meta);
        }

        return $models;
    }

    /**
     * Validate model configuration
     *
     * @param  string  $model  The model name
     * @param  array  $config  Configuration array to validate
     * @return bool Whether the configuration is valid
     */
    public static function validateConfig(string $model, array $config): bool
    {
        try {
            $service = self::make($model, $config);

            return $service->validateConfig($config);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear cached service instances
     */
    public static function clearCache(): void
    {
        self::$services = [];
    }
}
