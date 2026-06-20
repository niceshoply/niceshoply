<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\AI;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 标准 OpenAI 兼容通道（Laravel AI 通道）。
 *
 * 使用 Laravel 原生 Http 客户端对接任意「OpenAI Chat Completions 兼容」端点：
 * OpenAI、Azure OpenAI、OpenRouter、本地 Ollama / LM Studio、各类自建网关等，
 * 仅需配置 base_url + api_key + model 即可，无需额外 SDK 依赖。
 *
 * 同时支持一次性生成（generate）与服务端流式（stream，标准 SSE）。
 * 该服务承担「混合 AI 架构」中标准 OpenAI 兼容通道的角色，
 * 国产模型则通过 {@see LegacyAIServiceAdapter} 复用既有适配器。
 */
class LaravelAIService implements AIServiceInterface
{
    private array $config;

    /**
     * @param  array  $config  base_url、api_key、model、max_tokens、temperature、timeout、chat_path、system_prompt
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 一次性生成内容。
     *
     * @param  string  $prompt
     * @param  array  $options
     * @return string
     * @throws Exception
     */
    public function generate(string $prompt, array $options = []): string
    {
        $response = $this->request()
            ->acceptJson()
            ->post($this->endpoint(), $this->payload($prompt, $options, false));

        if ($response->failed()) {
            $message = 'Laravel AI 请求失败：HTTP '.$response->status();
            Log::error($message.' '.$response->body());
            throw new Exception($message);
        }

        return (string) ($response->json('choices.0.message.content') ?? '');
    }

    /**
     * 流式生成内容（逐 token / 逐片段产出）。
     *
     * 解析标准 SSE：逐行读取 `data: {json}`，提取 choices[0].delta.content。
     *
     * @param  string  $prompt
     * @param  array  $options
     * @return iterable<string>
     */
    public function stream(string $prompt, array $options = []): iterable
    {
        $response = $this->request()
            ->withOptions(['stream' => true])
            ->post($this->endpoint(), $this->payload($prompt, $options, true));

        $body   = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(2048);

            // 按行切分缓冲区，逐行解析 SSE
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if ($line === '' || ! str_starts_with($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, 5));
                if ($data === '[DONE]') {
                    return;
                }

                $json  = json_decode($data, true);
                $delta = $json['choices'][0]['delta']['content'] ?? null;
                if ($delta !== null && $delta !== '') {
                    yield $delta;
                }
            }
        }
    }

    /**
     * 配置校验：需提供 api_key 或 base_url（本地端点可无 key）。
     *
     * @param  array  $config
     * @return bool
     */
    public function validateConfig(array $config): bool
    {
        return ! empty($config['api_key']) || ! empty($config['base_url']);
    }

    /**
     * 模型信息。
     *
     * @return array
     */
    public static function getModelInfo(): array
    {
        return [
            'name'               => 'OpenAI 兼容通道 (Laravel AI)',
            'models'             => ['gpt-4o-mini', 'gpt-4o', 'gpt-3.5-turbo', 'custom'],
            'supports_streaming' => true,
            'supports_images'    => false,
            'max_tokens'         => 128000,
            'source'             => 'laravel_ai',
        ];
    }

    /**
     * 构造带鉴权与超时的 Http 客户端。
     */
    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout((int) ($this->config['timeout'] ?? 60));

        $apiKey = (string) ($this->config['api_key'] ?? '');
        if ($apiKey !== '') {
            $client = $client->withToken($apiKey);
        }

        return $client;
    }

    /**
     * 解析 chat/completions 端点地址。
     *
     * 规则：
     *  - 显式配置 chat_path 时直接拼接；
     *  - base_url 已含版本段（/v1、/compatible-mode/v1 等）→ 追加 /chat/completions；
     *  - 否则追加 /v1/chat/completions。
     */
    private function endpoint(): string
    {
        $base = rtrim((string) ($this->config['base_url'] ?? 'https://api.openai.com'), '/');

        if (! empty($this->config['chat_path'])) {
            return $base.'/'.ltrim((string) $this->config['chat_path'], '/');
        }

        if (preg_match('#/v\d+($|/)#', $base)) {
            return $base.'/chat/completions';
        }

        return $base.'/v1/chat/completions';
    }

    /**
     * 构造请求体。
     *
     * @param  string  $prompt
     * @param  array  $options
     * @param  bool  $stream
     * @return array
     */
    private function payload(string $prompt, array $options, bool $stream): array
    {
        $messages = [];

        $system = $options['system'] ?? ($this->config['system_prompt'] ?? null);
        if (! empty($system)) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return array_filter([
            'model'       => $options['model'] ?? $this->config['model'] ?? 'gpt-3.5-turbo',
            'messages'    => $messages,
            'max_tokens'  => (int) ($options['max_tokens'] ?? $this->config['max_tokens'] ?? 1000),
            'temperature' => (float) ($options['temperature'] ?? $this->config['temperature'] ?? 0.7),
            'stream'      => $stream ?: null,
        ], fn ($v) => $v !== null);
    }
}
