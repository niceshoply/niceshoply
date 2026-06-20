<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use NiceShoply\Common\Services\AI\AIProviderRegistry;
use NiceShoply\Common\Services\AI\AIServiceFactory;
use NiceShoply\Common\Services\AI\AIServiceInterface;
use Tests\TestCase;

/**
 * AI 提供方注册表测试（IMP-03 混合架构）
 */
class AIProviderRegistryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AIProviderRegistry::resetInstance();
        AIServiceFactory::clearCache();
    }

    protected function tearDown(): void
    {
        AIProviderRegistry::resetInstance();
        AIServiceFactory::clearCache();
        parent::tearDown();
    }

    /**
     * 通过类名注册自定义提供方并能被工厂解析。
     */
    public function test_register_and_resolve_via_factory(): void
    {
        $registry = AIProviderRegistry::getInstance();
        $registry->register('fake-llm', FakeAIService::class, [
            'name'         => '测试大模型',
            'capabilities' => ['text', 'image'],
        ]);

        $this->assertTrue($registry->has('fake-llm'));
        $this->assertTrue($registry->supports('fake-llm', 'image'));
        $this->assertFalse($registry->supports('fake-llm', 'audio'));

        // 工厂优先解析注册表中的提供方
        $service = AIServiceFactory::make('fake-llm', ['api_key' => 'x']);
        $this->assertInstanceOf(AIServiceInterface::class, $service);
        $this->assertSame('FAKE:你好', $service->generate('你好'));
    }

    /**
     * 通过闭包注册自定义提供方。
     */
    public function test_register_via_closure(): void
    {
        AIProviderRegistry::getInstance()->register('closure-llm', function (array $config) {
            return new FakeAIService($config);
        });

        $service = AIServiceFactory::make('closure-llm');
        $this->assertSame('FAKE:hi', $service->generate('hi'));
    }

    /**
     * 注册的提供方应出现在可用模型列表中。
     */
    public function test_registered_provider_appears_in_available_models(): void
    {
        AIProviderRegistry::getInstance()->register('listed-llm', FakeAIService::class, ['name' => '列表模型']);

        $models = AIServiceFactory::getAvailableModels();
        $this->assertArrayHasKey('listed-llm', $models);
        $this->assertSame('列表模型', $models['listed-llm']['name']);
    }
}

/**
 * 测试用伪 AI 服务。
 */
class FakeAIService implements AIServiceInterface
{
    public function __construct(private array $config = []) {}

    public function generate(string $prompt, array $options = []): string
    {
        return 'FAKE:'.$prompt;
    }

    public function stream(string $prompt, array $options = []): iterable
    {
        yield 'FAKE:'.$prompt;
    }

    public function validateConfig(array $config): bool
    {
        return true;
    }

    public static function getModelInfo(): array
    {
        return ['name' => 'Fake'];
    }
}
