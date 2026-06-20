<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\LegalDocument;
use NiceShoply\Common\Repositories\LegalDocumentRepo;

/**
 * 法律文档后台管理。
 */
class LegalDocumentController extends BaseController
{
    public function index(Request $request): mixed
    {
        return nice_view('console::legal_documents.index', [
            'criteria'  => LegalDocumentRepo::getCriteria(),
            'documents' => LegalDocumentRepo::getInstance()->list($request->all()),
        ]);
    }

    public function create(): mixed
    {
        return nice_view('console::legal_documents.form', [
            'document'    => new LegalDocument,
            'typeOptions' => LegalDocumentRepo::getTypeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            LegalDocumentRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('legal_documents.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('legal_documents.create'))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(LegalDocument $legalDocument): mixed
    {
        $legalDocument->load('translations');

        return nice_view('console::legal_documents.form', [
            'document'    => $legalDocument,
            'typeOptions' => LegalDocumentRepo::getTypeOptions(),
        ]);
    }

    public function update(Request $request, LegalDocument $legalDocument): RedirectResponse
    {
        try {
            LegalDocumentRepo::getInstance()->update($legalDocument, $this->normalize($request));

            return redirect(console_route('legal_documents.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('legal_documents.edit', [$legalDocument->id]))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(LegalDocument $legalDocument): RedirectResponse
    {
        try {
            LegalDocumentRepo::getInstance()->destroy($legalDocument);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(Request $request): array
    {
        return [
            'type'              => $request->input('type', LegalDocument::TYPE_PRIVACY),
            'version'           => $request->input('version', '1.0'),
            'active'            => (bool) $request->input('active', true),
            'require_reconsent' => (bool) $request->input('require_reconsent', true),
            'translations'      => $request->input('translations', []),
        ];
    }
}
