<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Tests\AI;

use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Services\AI\LaravelAIService;
use Tests\TestCase;

/**
 * LaravelAIService（OpenAI 兼容通道）测试。
 *
 * 通过 Http::fake 隔离外部调用，验证：
 *  - 一次性生成解析 choices[0].message.content
 *  - 流式解析标准 SSE 的 delta.content
 *  - 端点地址按 base_url 版本段智能拼接
 *  - 失败响应抛出异常
 */
class LaravelAIServiceTest extends TestCase
{
    public function test_generate_parses_content(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => '你好，世界']]],
            ], 200),
        ]);

        $service = new LaravelAIService([
            'api_key'  => 'sk-test',
            'base_url' => 'https://api.openai.com',
            'model'    => 'gpt-4o-mini',
        ]);

        $this->assertSame('你好，世界', $service->generate('hi'));

        Http::assertSent(fn ($req) => $req->url() === 'https://api.openai.com/v1/chat/completions');
    }

    public function test_generate_throws_on_failure(): void
    {
        Http::fake(['*' => Http::response('server error', 500)]);

        $service = new LaravelAIService(['api_key' => 'sk', 'base_url' => 'https://api.openai.com']);

        $this->expectException(\Exception::class);
        $service->generate('hi');
    }

    public function test_stream_parses_sse_deltas(): void
    {
        $sse = "data: {\"choices\":[{\"delta\":{\"content\":\"Hello\"}}]}\n\n"
             ."data: {\"choices\":[{\"delta\":{\"content\":\" World\"}}]}\n\n"
             ."data: [DONE]\n\n";

        Http::fake(['*' => Http::response($sse, 200)]);

        $service = new LaravelAIService(['api_key' => 'sk', 'base_url' => 'https://api.openai.com']);

        $chunks = [];
        foreach ($service->stream('hi') as $chunk) {
            $chunks[] = $chunk;
        }

        $this->assertSame(['Hello', ' World'], $chunks);
        $this->assertSame('Hello World', implode('', $chunks));
    }

    public function test_endpoint_respects_versioned_base_url(): void
    {
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => 'ok']]]], 200)]);

        // base_url 已含 /v1 版本段：应拼接 /chat/completions 而非 /v1/chat/completions
        $service = new LaravelAIService([
            'api_key'  => 'sk',
            'base_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
        ]);

        $service->generate('hi');

        Http::assertSent(fn ($req) => $req->url() === 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions');
    }

    public function test_validate_config_accepts_api_key_or_base_url(): void
    {
        $service = new LaravelAIService([]);

        $this->assertTrue($service->validateConfig(['api_key' => 'sk']));
        $this->assertTrue($service->validateConfig(['base_url' => 'http://localhost:11434']));
        $this->assertFalse($service->validateConfig([]));
    }
}
