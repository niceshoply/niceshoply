<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Services\AI\AIServiceManager;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * AI 流式生成（SSE）后台端点测试。
 *
 * 通过 Http::fake 模拟 OpenAI 兼容通道的 SSE 响应，验证：
 *  - 端点以 text/event-stream 推送增量片段并以 [DONE] 收尾
 *  - 空 prompt 返回错误事件
 *  - 无权限管理员 403
 */
class ContentAIStreamTest extends ConsoleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 默认模型走 laravel_ai 标准通道，便于用 Http::fake 隔离
        config([
            'nice.system.ai_model'            => 'laravel_ai',
            'nice.system.laravel_ai_enabled'  => true,
            'nice.system.laravel_ai_api_key'  => 'sk-test',
            'nice.system.laravel_ai_base_url' => 'https://api.openai.com',
            'nice.system.laravel_ai_model'    => 'gpt-4o-mini',
        ]);
        AIServiceManager::resetInstance();
    }

    protected function tearDown(): void
    {
        AIServiceManager::resetInstance();
        parent::tearDown();
    }

    public function test_stream_pushes_sse_chunks(): void
    {
        $sse = "data: {\"choices\":[{\"delta\":{\"content\":\"你好\"}}]}\n\n"
             ."data: {\"choices\":[{\"delta\":{\"content\":\"世界\"}}]}\n\n"
             ."data: [DONE]\n\n";
        Http::fake(['*' => Http::response($sse, 200)]);

        $this->loginAdmin(['content_ai_stream']);

        $response = $this->get($this->consoleUrl('content_ai.stream', ['prompt' => '写一句问候']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('你好', $content);
        $this->assertStringContainsString('世界', $content);
        $this->assertStringContainsString('[DONE]', $content);
    }

    public function test_stream_empty_prompt_returns_error_event(): void
    {
        Http::fake();

        $this->loginAdmin(['content_ai_stream']);

        $response = $this->get($this->consoleUrl('content_ai.stream', ['prompt' => '']));

        $response->assertOk();
        $this->assertStringContainsString('Empty prompt', $response->streamedContent());
    }

    public function test_stream_forbidden_without_permission(): void
    {
        Http::fake();

        $this->loginAdmin([]); // 不授予 content_ai_stream

        $this->get($this->consoleUrl('content_ai.stream', ['prompt' => 'hi']))
            ->assertStatus(403);
    }
}
