<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\AI;

use Closure;
use InvalidArgumentException;

/**
 * AI 提供方注册表（混合架构核心）
 *
 * 在内置适配器（OpenAI、DeepSeek、Kimi、豆包、千问、混元等）之外，
 * 允许插件或外部模块（如 laravel/ai 桥接）在运行期动态注册自定义 AI 提供方，
 * 实现「内置 + 第三方」混合可扩展架构。
 *
 * 解析优先级：自定义注册表 > 内置 modelMap。
 *
 * 使用示例（插件 boot 中）：
 *   AIProviderRegistry::getInstance()->register('my-llm', MyLlmService::class, [
 *       'name'         => '我的大模型',
 *       'capabilities' => ['text', 'image'],
 *   ]);
 */
class AIProviderRegistry
{
    private static ?AIProviderRegistry $instance = null;

    /**
     * 已注册的提供方解析器。
     *
     * @var array<string, string|Closure>
     */
    private array $resolvers = [];

    /**
     * 已注册提供方的元数据。
     *
     * @var array<string, array>
     */
    private array $meta = [];

    private function __construct() {}

    /**
     * 获取单例。
     *
     * @return AIProviderRegistry
     */
    public static function getInstance(): AIProviderRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 注册一个 AI 提供方。
     *
     * @param  string  $key  提供方唯一键（如 my-llm）
     * @param  string|Closure  $resolver  实现 AIServiceInterface 的类名，或返回该实例的闭包 fn(array $config): AIServiceInterface
     * @param  array  $meta  元数据：name、capabilities（['text','image'...]）等
     * @return void
     */
    public function register(string $key, string|Closure $resolver, array $meta = []): void
    {
        if (is_string($resolver) && ! is_subclass_of($resolver, AIServiceInterface::class)) {
            throw new InvalidArgumentException("AI 提供方类必须实现 AIServiceInterface：{$resolver}");
        }

        $this->resolvers[$key] = $resolver;
        $this->meta[$key]      = array_merge([
            'name'         => $key,
            'capabilities' => ['text'],
            'source'       => 'custom',
        ], $meta);
    }

    /**
     * 注销一个提供方。
     *
     * @param  string  $key
     * @return void
     */
    public function unregister(string $key): void
    {
        unset($this->resolvers[$key], $this->meta[$key]);
    }

    /**
     * 是否已注册指定提供方。
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->resolvers[$key]);
    }

    /**
     * 解析并实例化一个提供方。
     *
     * @param  string  $key
     * @param  array  $config
     * @return AIServiceInterface
     */
    public function resolve(string $key, array $config = []): AIServiceInterface
    {
        if (! $this->has($key)) {
            throw new InvalidArgumentException("未注册的 AI 提供方：{$key}");
        }

        $resolver = $this->resolvers[$key];

        $service = $resolver instanceof Closure
            ? $resolver($config)
            : new $resolver($config);

        if (! $service instanceof AIServiceInterface) {
            throw new InvalidArgumentException('AI 提供方必须实现 AIServiceInterface');
        }

        return $service;
    }

    /**
     * 是否支持指定能力（如 image）。
     *
     * @param  string  $key
     * @param  string  $capability
     * @return bool
     */
    public function supports(string $key, string $capability): bool
    {
        return in_array($capability, $this->meta[$key]['capabilities'] ?? [], true);
    }

    /**
     * 获取全部已注册提供方的元数据。
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        return $this->meta;
    }

    /**
     * 获取全部已注册提供方键。
     *
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->resolvers);
    }

    /**
     * 重置注册表（便于测试）。
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
