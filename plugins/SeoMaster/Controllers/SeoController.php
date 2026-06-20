<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SeoMaster\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Plugin\SeoMaster\Services\SeoService;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        if (! (bool) plugin_setting('seo_master', 'enable_sitemap', true)) {
            return response('Not Found', 404);
        }

        $xml = SeoService::getInstance()->sitemapXml();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $content = SeoService::getInstance()->robots();

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
