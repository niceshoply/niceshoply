<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Tests\AI;

use NiceShoply\Common\Services\AI\AIServiceInterface;
use NiceShoply\Common\Services\AI\LegacyAIServiceAdapter;
use Tests\TestCase;

/**
 * LegacyAIServiceAdapter 测试。
 *
 * 验证装饰器的流式优雅降级：
 *  - 底层正常流式 → 透传分片
 *  - 底层流式抛错且未产出 → 回退整段 generate
 *  - 底层流式为空 → 回退整段 generate
 *  - generate / validateConfig 透传
 */
class LegacyAIServiceAdapterTest extends TestCase
{
    private function innerWith(callable $streamFn, string $generateResult = 'FULL'): AIServiceInterface
    {
        return new class($streamFn, $generateResult) implements AIServiceInterface
        {
            public function __construct(private $streamFn, private string $generateResult) {}

            public function generate(string $prompt, array $options = []): string
            {
                return $this->generateResult;
            }

            public function stream(string $prompt, array $options = []): iterable
            {
                return ($this->streamFn)();
            }

            public function validateConfig(array $config): bool
            {
                return true;
            }

            public static function getModelInfo(): array
            {
                return ['name' => 'inner'];
            }
        };
    }

    public function test_streams_through_when_inner_streams(): void
    {
        $inner = $this->innerWith(fn () => (function () {
            yield 'A';
            yield 'B';
        })());
        $adapter = new LegacyAIServiceAdapter($inner, 'deepseek');

        $chunks = iterator_to_array($adapter->stream('hi'), false);

        $this->assertSame(['A', 'B'], $chunks);
    }

    public function test_falls_back_to_generate_when_stream_throws_before_yield(): void
    {
        $inner = $this->innerWith(fn () => (function () {
            throw new \RuntimeException('not supported');
            yield; // unreachable, makes it a generator
        })(), 'FALLBACK');

        $adapter = new LegacyAIServiceAdapter($inner, 'kimi');

        $chunks = iterator_to_array($adapter->stream('hi'), false);

        $this->assertSame(['FALLBACK'], $chunks);
    }

    public function test_falls_back_to_generate_when_stream_empty(): void
    {
        $inner = $this->innerWith(fn () => (function () {
            if (false) {
                yield;
            }
        })(), 'WHOLE');
        $adapter = new LegacyAIServiceAdapter($inner, 'doubao');

        $chunks = iterator_to_array($adapter->stream('hi'), false);

        $this->assertSame(['WHOLE'], $chunks);
    }

    public function test_generate_and_validate_passthrough(): void
    {
        $inner = $this->innerWith(fn () => (function () {
            yield 'x';
        })(), 'RESULT');
        $adapter = new LegacyAIServiceAdapter($inner, 'qianwen');

        $this->assertSame('RESULT', $adapter->generate('hi'));
        $this->assertTrue($adapter->validateConfig([]));
        $this->assertSame($inner, $adapter->getInner());
    }
}
