<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\GdprRequest;
use NiceShoply\Common\Models\LegalDocument;
use NiceShoply\Common\Repositories\GdprRequestRepo;
use NiceShoply\Common\Repositories\LegalDocumentRepo;
use NiceShoply\Common\Services\Compliance\GdprService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 账户隐私与 GDPR 自助服务。
 */
class PrivacyController extends Controller
{
    public function index(): mixed
    {
        $customer = current_customer();
        $exports  = GdprRequest::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $pendingConsents = [];
        foreach ([LegalDocument::TYPE_PRIVACY, LegalDocument::TYPE_TERMS] as $type) {
            $doc = LegalDocumentRepo::getInstance()->getActiveByType($type);
            if ($doc && $doc->require_reconsent && ! LegalDocumentRepo::getInstance()->hasConsented($customer->id, $doc)) {
                $pendingConsents[] = $doc;
            }
        }

        return nice_view('account.privacy', [
            'customer'        => $customer,
            'gdprRequests'    => $exports,
            'pendingConsents' => $pendingConsents,
        ]);
    }

    public function consent(Request $request): mixed
    {
        try {
            $customer = current_customer();
            $type     = $request->input('type', LegalDocument::TYPE_PRIVACY);
            $document = LegalDocumentRepo::getInstance()->getActiveByType($type);
            if (! $document) {
                throw new Exception(trans('front/privacy.document_not_found'));
            }

            LegalDocumentRepo::getInstance()->recordConsent(
                $customer->id,
                $document,
                (string) $request->ip(),
                substr((string) $request->userAgent(), 0, 512)
            );

            return json_success(trans('front/privacy.consent_saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function export(Request $request): mixed
    {
        try {
            $customer = current_customer();
            GdprService::getInstance()->requestExport($customer, (string) $request->ip());

            return json_success(trans('front/privacy.export_requested'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function delete(Request $request): mixed
    {
        try {
            $customer = current_customer();
            GdprService::getInstance()->requestDelete($customer, (string) $request->ip());

            return json_success(trans('front/privacy.delete_requested'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function download(int $id): BinaryFileResponse
    {
        $customer = current_customer();
        $gdpr     = GdprRequestRepo::getInstance()->detail($id);

        abort_if(! $gdpr || $gdpr->customer_id !== $customer->id, 403);
        abort_if($gdpr->status !== GdprRequest::STATUS_COMPLETED || $gdpr->file_path === '', 404);

        $path = storage_path('app/'.$gdpr->file_path);
        abort_if(! is_file($path), 404);

        return response()->download($path, 'my-data.zip');
    }
}
