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
use NiceShoply\Common\Models\State;
use NiceShoply\Common\Repositories\StateRepo;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getStates();
        if ($items) {
            State::query()->truncate();

            $chunks = array_chunk($items, ceil(count($items) / 6));
            foreach ($chunks as $states) {
                StateRepo::getInstance()->createMany($states);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getStates(): array
    {
        $file   = database_path('seeders/data/states.json');
        $result = json_decode(file_get_contents($file), true);

        return $result['data'] ?? [];
    }
}
