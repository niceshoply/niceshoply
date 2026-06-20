<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\LegalDocument;
use NiceShoply\Common\Repositories\LegalDocumentRepo;

/**
 * Cookie 偏好存储（前台横幅回调）。
 */
class CookieConsentController extends Controller
{
    public function store(Request $request): mixed
    {
        $essential = true;
        $analytics = (bool) $request->input('analytics', false);
        $marketing = (bool) $request->input('marketing', false);

        $payload = json_encode([
            'essential' => $essential,
            'analytics' => $analytics,
            'marketing' => $marketing,
            'ts'        => time(),
        ]);

        return response()->json(['success' => true])
            ->cookie('niceshoply_cookie_consent', $payload, 60 * 24 * 365, '/', null, false, false);
    }

    /**
     * 登录用户同步 Cookie 偏好到服务端（可选记录）。
     */
    public function sync(Request $request): mixed
    {
        if ($customer = current_customer()) {
            $cookieDoc = LegalDocumentRepo::getInstance()->getActiveByType(LegalDocument::TYPE_COOKIE);
            if ($cookieDoc) {
                LegalDocumentRepo::getInstance()->recordConsent(
                    $customer->id,
                    $cookieDoc,
                    (string) $request->ip(),
                    'cookie-banner'
                );
            }
        }

        return json_success('ok');
    }
}
