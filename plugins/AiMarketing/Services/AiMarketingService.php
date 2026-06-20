<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiMarketing\Services;

use RuntimeException;
use NiceShoply\Common\Services\AI\AIServiceManager;
use Plugin\AiMarketing\Models\AiMarketingLog;

class AiMarketingService
{
    public const SCENES = [
        'product_title',
        'product_desc',
        'selling_point',
        'seo_meta',
        'sms',
        'email',
        'social',
    ];

    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 生成营销文案。复用核心 AIServiceManager（自带主/备模型故障转移）。
     *
     * @param  array{tone?:string, keywords?:string, lang?:string}  $options
     */
    public function generate(string $scene, string $input, array $options = [], int $operatorId = 0): string
    {
        if (! in_array($scene, self::SCENES, true)) {
            throw new RuntimeException(__('AiMarketing::common.invalid_scene'));
        }

        if (! class_exists(AIServiceManager::class)) {
            throw new RuntimeException(__('AiMarketing::common.ai_unavailable'));
        }

        $tone   = $options['tone'] ?? plugin_setting('ai_marketing', 'default_tone', 'planting');
        $prompt = $this->buildPrompt($scene, $input, $tone, $options);

        $output = AIServiceManager::getInstance()->generate($prompt, $this->purposeOf($scene), [
            'temperature' => 0.8,
            'max_tokens'  => 1200,
        ]);

        $output = trim((string) $output);

        AiMarketingLog::query()->create([
            'scene'       => $scene,
            'provider'    => (string) plugin_setting('ai_marketing', 'default_provider', ''),
            'input'       => $input,
            'output'      => $output,
            'operator_id' => $operatorId,
        ]);

        return $output;
    }

    protected function purposeOf(string $scene): string
    {
        return $scene === 'seo_meta' ? 'tdk' : 'chat';
    }

    protected function toneText(string $tone): string
    {
        return match ($tone) {
            'pro'   => '专业、客观、强调参数与品质',
            'promo' => '促销、紧迫感、突出优惠与限时',
            default => '种草、有亲和力、口语化、激发购买欲',
        };
    }

    protected function buildPrompt(string $scene, string $input, string $tone, array $options): string
    {
        $toneText = $this->toneText($tone);
        $keywords = trim((string) ($options['keywords'] ?? ''));
        $lang     = trim((string) ($options['lang'] ?? '中文'));
        $kwLine   = $keywords !== '' ? "需自然融入关键词：{$keywords}。" : '';

        return match ($scene) {
            'product_title' => "你是资深电商运营。请基于以下商品信息，生成 3 个高转化的商品标题，每行一个，控制在 30 字内，{$toneText}。{$kwLine}输出语言：{$lang}。\n商品信息：{$input}",
            'product_desc' => "你是资深电商文案。请基于以下商品信息撰写一段商品详情描述，约 150-250 字，{$toneText}。{$kwLine}输出语言：{$lang}。\n商品信息：{$input}",
            'selling_point' => "请基于以下商品信息，提炼 5 条核心卖点，每条一行、以「•」开头、简洁有力，{$toneText}。输出语言：{$lang}。\n商品信息：{$input}",
            'seo_meta' => "请为以下页面生成 SEO 的 Meta 标题(<=60字符)与 Meta 描述(<=155字符)，先输出『标题：』再输出『描述：』，关键词友好。{$kwLine}输出语言：{$lang}。\n页面信息：{$input}",
            'sms' => "请撰写一条营销短信文案，<=60 字，{$toneText}，需合规(含品牌词与退订提示占位『回T退订』)。输出语言：{$lang}。\n活动信息：{$input}",
            'email' => "请撰写一封营销邮件，输出『主题：』(2 个备选)与『正文：』，正文 200 字内，{$toneText}。输出语言：{$lang}。\n活动信息：{$input}",
            'social' => "请生成一条小红书/抖音风格的社媒种草文案，{$toneText}，结尾给出 5 个话题标签(#开头)。输出语言：{$lang}。\n商品/活动信息：{$input}",
            default => $input,
        };
    }
}
