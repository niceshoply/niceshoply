<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrivacyConsent\Controllers\Console;

use NiceShoply\Console\Controllers\BaseController;
use Plugin\PrivacyConsent\Models\Consent;

class PrivacyConsentController extends BaseController
{
    protected string $modelClass = Consent::class;

    public function index(): mixed
    {
        $consents = Consent::query()->orderByDesc('id')->paginate(50);
        $accepted = Consent::query()->where('choice', 'accept')->count();
        $rejected = Consent::query()->where('choice', 'reject')->count();

        return nice_view('PrivacyConsent::console.index', compact('consents', 'accepted', 'rejected'));
    }
}
