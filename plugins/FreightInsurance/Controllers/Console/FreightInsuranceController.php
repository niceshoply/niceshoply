<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance\Controllers\Console;

use NiceShoply\Console\Controllers\BaseController;
use Plugin\FreightInsurance\Models\InsuranceRecord;

class FreightInsuranceController extends BaseController
{
    protected string $modelClass = InsuranceRecord::class;

    public function index(): mixed
    {
        $records = InsuranceRecord::query()->orderByDesc('id')->paginate(30);
        $total   = (float) InsuranceRecord::query()->sum('premium');

        return nice_view('FreightInsurance::console.index', compact('records', 'total'));
    }
}
