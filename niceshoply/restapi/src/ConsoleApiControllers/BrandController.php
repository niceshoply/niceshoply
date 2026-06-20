<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Repositories\BrandRepo;
use NiceShoply\Common\Resources\BrandName;
use NiceShoply\Common\Resources\BrandSimple;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;

class BrandController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = BrandRepo::getInstance()->builder($filters)->paginate($perPage);

        return BrandSimple::collection($categories);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function names(Request $request): AnonymousResourceCollection
    {
        $brands = BrandRepo::getInstance()->getListByBrandIDs($request->get('ids'));

        return BrandName::collection($brands);
    }

    /**
     * Fuzzy search for auto complete.
     * /api/console/brands/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $categories = BrandRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return BrandSimple::collection($categories);
    }
}
