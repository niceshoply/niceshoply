<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\TranslatorAi\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\TranslatorAi\Services\TranslatorService;

class TranslatorController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('TranslatorAi::console.index');
    }

    /**
     * 翻译纯文本。
     */
    public function text(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'text'   => 'required|string|max:8000',
                'target' => 'required|string|max:32',
                'source' => 'nullable|string|max:32',
            ]);

            $output = TranslatorService::getInstance()->translateText(
                $data['text'],
                $data['target'],
                $data['source'] ?? 'auto'
            );

            return json_success('ok', ['output' => $output]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 翻译 key=value（每行一条）批量文案，并导出 PHP 语言数组。
     */
    public function lines(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'lines'  => 'required|string|max:20000',
                'target' => 'required|string|max:32',
                'source' => 'nullable|string|max:32',
            ]);

            $lines = $this->parseKeyValue($data['lines']);
            if (empty($lines)) {
                return json_fail(__('TranslatorAi::common.no_lines'));
            }

            $res = TranslatorService::getInstance()->translateLines(
                $lines,
                $data['target'],
                $data['source'] ?? 'auto'
            );

            return json_success('ok', [
                'result'   => $res['result'],
                'warnings' => $res['warnings'],
                'php'      => TranslatorService::getInstance()->exportPhp($res['result']),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 解析「key = value」每行一条的文本为关联数组。
     *
     * @return array<string,string>
     */
    protected function parseKeyValue(string $text): array
    {
        $lines  = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if ($key !== '') {
                $result[$key] = trim($value);
            }
        }

        return $result;
    }
}
