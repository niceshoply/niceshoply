<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use NiceShoply\Common\Models\Country;
use NiceShoply\Common\Models\Region;
use NiceShoply\Common\Models\State;
use NiceShoply\Common\Repositories\RegionRepo;

class RegionSeeder extends Seeder
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        $items = $this->getRegions();
        if ($items) {
            Region::query()->truncate();
            foreach ($items as $item) {
                RegionRepo::getInstance()->create($item);
            }
        }
    }

    /**
     * @return array
     */
    private function getRegions(): array
    {
        $country = Country::query()->where('code', 'US')->first();
        $state   = State::query()->where('country_id', $country->id)->where('code', 'CA')->first();

        return [
            [
                'name'          => 'US-CA',
                'description'   => 'California, United States of America',
                'position'      => 0,
                'active'        => true,
                'region_states' => [
                    [
                        'country_id' => $country->id,
                        'state_id'   => $state->id,
                    ],
                ],
            ],
        ];
    }
}
