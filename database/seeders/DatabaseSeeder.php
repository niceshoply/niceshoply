<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * Run all seeders: `php artisan db:seed`
 * Run one seeder: `php artisan db:seed --class=ProductSeeder`
 * Run demo data only: `php artisan db:seed --class=DemoDataSeeder`
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            LocaleSeeder::class,
            CurrencySeeder::class,
            WeightClassSeeder::class,

            AdminSeeder::class,
            ArticleSeeder::class,
            AttributeSeeder::class,
            BrandSeeder::class,
            CatalogSeeder::class,
            CategorySeeder::class,
            CountrySeeder::class,
            CustomerGroupSeeder::class,
            PageSeeder::class,
            OptionSeeder::class,
            ProductSeeder::class,
            StateSeeder::class,
            TagSeeder::class,
            RegionSeeder::class,
            TaxSeeder::class,
            PluginSeeder::class,

            // 野径户外 WildPath 演示数据（与 InnoShop 服装演示完全不同）
            WarehouseSeeder::class,
            ReturnReasonSeeder::class,
            DemoDataSeeder::class,
        ]);

        touch(storage_path('installed'));
    }
}
