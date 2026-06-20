<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SearchPlus\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\SearchPlus\Services\SearchService;

class SearchController extends BaseController
{
    public function search(Request $request): mixed
    {
        $q = (string) $request->input('q', '');

        return json_success('ok', SearchService::getInstance()->search($q));
    }

    public function hotWords(): mixed
    {
        return json_success('ok', ['keywords' => SearchService::getInstance()->hotWords(10)]);
    }

    public function suggest(Request $request): mixed
    {
        $q = (string) $request->input('q', '');

        return json_success('ok', ['suggestions' => SearchService::getInstance()->suggest($q, 10)]);
    }
}
