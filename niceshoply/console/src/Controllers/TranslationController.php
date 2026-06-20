<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use NiceShoply\Console\Requests\TranslateRequest;
use NiceShoply\Console\Services\TranslatorService;

class TranslationController extends Controller
{
    /**
     * Translate text.
     *
     * @param  TranslateRequest  $request
     * @return mixed
     */
    public function translateText(TranslateRequest $request): mixed
    {
        return $this->translate($request, 'text');
    }

    /**
     * Translate HTML text.
     *
     * @param  TranslateRequest  $request
     * @return mixed
     */
    public function translateHtml(TranslateRequest $request): mixed
    {
        return $this->translate($request, 'html');
    }

    /**
     * Handle translation request.
     *
     * @param  TranslateRequest  $request
     * @param  string  $type
     * @return mixed
     */
    private function translate(TranslateRequest $request, string $type): mixed
    {
        try {
            $source = $request->get('source');
            $target = $request->get('target');
            $text   = $request->get('text');

            register('translation_type', $type);

            $response = TranslatorService::getInstance()->translate($source, $target, $text);

            return create_json_success($response);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
