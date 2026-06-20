<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\AttributeRepo;
use NiceShoply\Common\Resources\AttributeSimple;

class AttributeController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters    = $request->all();
        $attributes = AttributeRepo::getInstance()->all($filters);
        $items      = AttributeSimple::collection($attributes);

        return read_json_success($items);
    }
}
