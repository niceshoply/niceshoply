<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\AiMarketing\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\AiMarketing\Models\AiMarketingLog;
use Plugin\AiMarketing\Services\AiMarketingService;

class AiMarketingController extends BaseController
{
    protected string $modelClass = AiMarketingLog::class;

    public function index(): mixed
    {
        $logs = AiMarketingLog::query()->orderByDesc('id')->limit(20)->get();

        return nice_view('AiMarketing::console.index', compact('logs'));
    }

    public function generate(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'scene'    => 'required|string',
                'input'    => 'required|string|max:4000',
                'tone'     => 'nullable|string',
                'keywords' => 'nullable|string|max:191',
                'lang'     => 'nullable|string|max:32',
            ]);

            $output = AiMarketingService::getInstance()->generate(
                $data['scene'],
                $data['input'],
                [
                    'tone'     => $data['tone'] ?? '',
                    'keywords' => $data['keywords'] ?? '',
                    'lang'     => $data['lang'] ?? '中文',
                ],
                (int) (auth()->id() ?? 0)
            );

            return json_success('ok', ['output' => $output]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
