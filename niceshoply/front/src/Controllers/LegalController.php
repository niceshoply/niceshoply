<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use NiceShoply\Common\Models\LegalDocument;
use NiceShoply\Common\Repositories\LegalDocumentRepo;

/**
 * 前台法律文档展示页。
 */
class LegalController extends Controller
{
    public function privacy(): mixed
    {
        return $this->show(LegalDocument::TYPE_PRIVACY);
    }

    public function terms(): mixed
    {
        return $this->show(LegalDocument::TYPE_TERMS);
    }

    public function cookie(): mixed
    {
        return $this->show(LegalDocument::TYPE_COOKIE);
    }

    /**
     * 开源许可说明页（MIT，突出完整源码所有权）。
     */
    public function openSource(): mixed
    {
        $licenseText = @file_get_contents(base_path('LICENSE'))
            ?: @file_get_contents(base_path('LICENSE.txt'))
            ?: '';

        return nice_view('legal.open_source', [
            'licenseText' => $licenseText,
            'updatedAt'   => '2026-06-14',
        ]);
    }

    /**
     * 商业授权说明页（去版权 / 白标，可选购买）。
     */
    public function commercial(): mixed
    {
        return nice_view('legal.commercial', [
            'updatedAt' => '2026-06-14',
            'marketplaceUrl' => config('niceshoply.api_url', 'https://marketplace.niceshoply.com'),
        ]);
    }

    private function show(string $type): mixed
    {
        $document = LegalDocumentRepo::getInstance()->getActiveByType($type);
        abort_if(! $document, 404);

        $document->load('translation');

        return nice_view('legal.show', [
            'document' => $document,
        ]);
    }
}
