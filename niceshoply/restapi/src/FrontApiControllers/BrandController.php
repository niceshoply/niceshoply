<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Repositories\BrandRepo;
use NiceShoply\Common\Resources\BrandSimple;

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

        $brands = BrandRepo::getInstance()->withActive()->builder($filters)->paginate($perPage);

        return BrandSimple::collection($brands);
    }

    /**
     * @return mixed
     */
    public function group(): mixed
    {
        $brands     = BrandRepo::getInstance()->withActive()->all();
        $collection = BrandSimple::collection($brands)->jsonSerialize();

        if (empty($collection)) {
            return read_json_success([]);
        }

        $items = [];
        foreach ($collection as $item) {
            $items[$item['first']]['name']     = $item['first'];
            $items[$item['first']]['brands'][] = $item;
        }
        $items = array_values($items);

        return read_json_success($items);
    }
}
