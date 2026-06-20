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
use NiceShoply\Common\Repositories\Attribute\ValueRepo;
use NiceShoply\Common\Resources\AttributeValueSimple;

class AttributeValueController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $values  = ValueRepo::getInstance()->all($filters);
        $items   = AttributeValueSimple::collection($values);

        return read_json_success($items);
    }
}
