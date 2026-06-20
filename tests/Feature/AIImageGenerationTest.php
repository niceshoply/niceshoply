<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use NiceShoply\Common\Services\AI\AIImageServiceInterface;
use NiceShoply\Common\Services\AI\AIProviderRegistry;
use NiceShoply\Common\Services\AI\AIServiceFactory;
use NiceShoply\Common\Services\AI\AIServiceInterface;
use NiceShoply\Common\Services\AI\AIServiceManager;
use Tests\TestCase;

/**
 * AI 图片生成测试（IMP-17）
 *
 * 通过注册一个伪图片提供方，验证 AIServiceManager::generateImage
 * 能正确解码并保存到 media 磁盘并返回可访问 URL。
 */
class AIImageGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AIProviderRegistry::resetInstance();
        AIServiceFactory::clearCache();
        Storage::fake('media');
    }

    protected function tearDown(): void
    {
        AIProviderRegistry::resetInstance();
        AIServiceFactory::clearCache();
        parent::tearDown();
    }

    /**
     * 生成图片应保存文件并返回 url/path/model。
     */
    public function test_generate_image_saves_to_media_disk(): void
    {
        AIProviderRegistry::getInstance()->register('fake-image', FakeImageService::class, [
            'name'         => '伪图片模型',
            'capabilities' => ['image'],
        ]);

        $result = AIServiceManager::getInstance()->generateImage('一只柴犬', [
            'model'     => 'fake-image',
            'save_path' => 'ai-images',
        ]);

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertSame('fake-image', $result['model']);

        // 文件确实写入 media 磁盘
        Storage::disk('media')->assertExists($result['path']);
        $this->assertStringStartsWith('ai-images/', $result['path']);
    }

    /**
     * 不支持图片能力的模型应抛出异常。
     */
    public function test_non_image_model_throws(): void
    {
        AIProviderRegistry::getInstance()->register('text-only', TextOnlyService::class);

        $this->expectException(\RuntimeException::class);

        AIServiceManager::getInstance()->generateImage('x', ['model' => 'text-only']);
    }
}

/**
 * 伪图片服务：返回 1x1 透明 PNG 的 base64。
 */
class FakeImageService implements AIImageServiceInterface, AIServiceInterface
{
    public function __construct(private array $config = []) {}

    public function generate(string $prompt, array $options = []): string
    {
        return '';
    }

    public function stream(string $prompt, array $options = []): iterable
    {
        yield '';
    }

    public function validateConfig(array $config): bool
    {
        return true;
    }

    public static function getModelInfo(): array
    {
        return ['name' => 'FakeImage'];
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        // 1x1 透明 PNG
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        return ['b64_json' => $png, 'model' => 'fake-image'];
    }
}

/**
 * 仅文本服务：不实现图片接口。
 */
class TextOnlyService implements AIServiceInterface
{
    public function __construct(private array $config = []) {}

    public function generate(string $prompt, array $options = []): string
    {
        return 'text';
    }

    public function stream(string $prompt, array $options = []): iterable
    {
        yield 'text';
    }

    public function validateConfig(array $config): bool
    {
        return true;
    }

    public static function getModelInfo(): array
    {
        return ['name' => 'TextOnly'];
    }
}
