<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Models\Country;
use NiceShoply\Common\Repositories\CountryRepo;
use NiceShoply\Common\Repositories\StateRepo;
use NiceShoply\Common\Resources\CountrySimple;
use NiceShoply\Console\Controllers\BaseController;

class CountryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $countries = CountryRepo::getInstance()->getCountries($request->all());

        return CountrySimple::collection($countries);
    }

    /**
     * @param  string  $code
     * @return mixed
     */
    public function show(string $code): mixed
    {
        $country = Country::query()->where('code', $code)->orWhere('id', $code)->first();
        if (empty($country)) {
            return collect();
        }

        $filters = [
            'country_id' => $country->id,
        ];
        $countries = StateRepo::getInstance()->builder($filters)->get();

        return CountrySimple::collection($countries);
    }
}
