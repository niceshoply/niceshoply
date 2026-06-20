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
use Illuminate\Support\Facades\Hash;
use NiceShoply\Common\Models\Admin;

class AdminSeeder extends Seeder
{
    /** 演示管理员默认密码 */
    private const DEMO_PASSWORD = '123456789';

    public function run(): void
    {
        $items = $this->getAdmins();
        if ($items) {
            Admin::query()->truncate();
            foreach ($items as $item) {
                Admin::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getAdmins(): array
    {
        return [
            [
                'name'     => 'admin',
                'email'    => 'admin@niceshoply.com',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'active'   => true,
                'locale'   => 'en',
            ],
        ];
    }
}
