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
use NiceShoply\Common\Models\Country;
use NiceShoply\Common\Repositories\CountryRepo;
use NiceShoply\Common\Repositories\StateRepo;
use NiceShoply\Common\Resources\CountrySimple;
use NiceShoply\Common\Resources\StateItem;

class CountryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $countries = CountryRepo::getInstance()->builder($request->all())->get();

        return CountrySimple::collection($countries);
    }

    /**
     * @param  Country  $country
     * @return AnonymousResourceCollection
     */
    public function states(Country $country): AnonymousResourceCollection
    {
        $filters = [
            'country_id' => $country->id,
        ];
        $states = StateRepo::getInstance()->builder($filters)->get();

        return StateItem::collection($states);
    }
}
