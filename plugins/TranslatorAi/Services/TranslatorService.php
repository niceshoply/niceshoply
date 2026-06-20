<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\TranslatorAi\Services;

use RuntimeException;
use NiceShoply\Common\Services\AI\AIServiceManager;

class TranslatorService
{
    /** 常见占位符：:name、{count}、%s、:Name */
    protected const PLACEHOLDER_PATTERN = '/(:[A-Za-z_]+|\{[A-Za-z0-9_]+\}|%[sd@])/';

    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 翻译一组 key => value 文案，返回 key => translated。
     *
     * @param  array<string,string>  $lines
     * @return array{result: array<string,string>, warnings: array<int,string>}
     */
    public function translateLines(array $lines, string $targetLang, string $sourceLang = 'auto'): array
    {
        if (empty($lines)) {
            return ['result' => [], 'warnings' => []];
        }

        $payload = json_encode($lines, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $prompt  = $this->buildPrompt($payload, $targetLang, $sourceLang);

        $raw    = $this->callAI($prompt, 'chat');
        $parsed = $this->parseJson($raw);

        $result   = [];
        $warnings = [];
        foreach ($lines as $key => $source) {
            $translated   = $parsed[$key] ?? $source;
            $result[$key] = $translated;

            if (! $this->placeholdersMatch($source, $translated)) {
                $warnings[] = $key;
            }
        }

        return ['result' => $result, 'warnings' => $warnings];
    }

    /**
     * 翻译一段纯文本。
     */
    public function translateText(string $text, string $targetLang, string $sourceLang = 'auto'): string
    {
        if (trim($text) === '') {
            return '';
        }

        $glossary = $this->glossaryHint();
        $prompt   = "你是专业本地化译员。请将下面的内容翻译为「{$targetLang}」"
            .($sourceLang !== 'auto' ? "（源语言：{$sourceLang}）" : '')
            .'。只输出译文本身，不要解释，保留所有占位符(如 :name、{count}、%s)原样不译。'
            .$glossary
            ."\n原文：\n".$text;

        return trim($this->callAI($prompt, 'chat'));
    }

    protected function buildPrompt(string $payloadJson, string $targetLang, string $sourceLang): string
    {
        $glossary = $this->glossaryHint();

        return "你是专业本地化译员。请把下面 JSON 对象中每个 value 翻译为「{$targetLang}」"
            .($sourceLang !== 'auto' ? "（源语言：{$sourceLang}）" : '')
            .'，保持 key 不变。严格遵守：'
            .'1) 占位符(如 :name、{count}、%s、:Name)必须原样保留、位置语义正确；'
            .'2) 不要翻译 HTML 标签与变量；'
            .'3) 只输出合法 JSON，不要任何额外说明或代码块标记。'
            .$glossary
            ."\nJSON：\n".$payloadJson;
    }

    protected function glossaryHint(): string
    {
        $glossary = trim((string) plugin_setting('translator_ai', 'glossary', ''));
        if ($glossary === '') {
            return '';
        }

        return '术语表(保留或按指定译法)：'.str_replace(["\r\n", "\n"], '；', $glossary).'。';
    }

    protected function callAI(string $prompt, string $purpose): string
    {
        if (! class_exists(AIServiceManager::class)) {
            throw new RuntimeException(__('TranslatorAi::common.ai_unavailable'));
        }

        return (string) AIServiceManager::getInstance()->generate($prompt, $purpose, [
            'temperature' => 0.2,
            'max_tokens'  => 2000,
        ]);
    }

    /**
     * 解析模型返回的 JSON（容错去除 ```json 包裹）。
     *
     * @return array<string,string>
     */
    protected function parseJson(string $raw): array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?|```$/m', '', $raw) ?? $raw;

        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $raw = substr($raw, $start, $end - $start + 1);
        }

        $data = json_decode(trim($raw), true);

        return is_array($data) ? $data : [];
    }

    protected function placeholdersMatch(string $source, string $translated): bool
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $source, $sm);
        preg_match_all(self::PLACEHOLDER_PATTERN, $translated, $tm);

        $a = $sm[0];
        $b = $tm[0];
        sort($a);
        sort($b);

        return $a === $b;
    }

    /**
     * 将 key => value 数组导出为 Laravel 语言文件内容。
     *
     * @param  array<string,string>  $lines
     */
    public function exportPhp(array $lines): string
    {
        $body = '';
        foreach ($lines as $key => $value) {
            $k = addslashes((string) $key);
            $v = addslashes((string) $value);
            $body .= "    '{$k}' => '{$v}',\n";
        }

        return "<?php\n\nreturn [\n{$body}];\n";
    }
}
