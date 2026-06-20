<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * 野径户外 WildPath 扩展演示数据
 * 覆盖：客户、订单、退货、评价、钱包、多仓库存、调拨、收藏等运营功能
 *
 * 用法：php artisan db:seed --class=DemoDataSeeder
 * 或在 DatabaseSeeder 中一并执行
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /** 演示账号统一密码 */
    private const DEMO_PASSWORD = '123456789';

    /** @var array<int, string> */
    private array $locales = ['en', 'zh-cn'];

    private int $now;

    /** @var array<int, string> 扩展文章 slug，用于重复执行时精准清理 */
    private array $extraArticleSlugs = [
        'winter-camping-essential-gear',
        'how-to-choose-sleeping-bag',
        'leave-no-trace-principles',
        'kailash-trek-preparation',
        'waterproof-jacket-comparison',
        'headlamp-lumen-guide',
        'family-camping-first-timer',
        'backpack-fitting-guide',
    ];

    public function run(): void
    {
        $this->now = time();

        // MySQL 专用：放宽 strict mode；SQLite 无需此设置
        if (DB::getDriverName() === 'mysql') {
            DB::statement("SET SESSION sql_mode=''");
        }

        $this->command->info('清理上次扩展演示数据...');
        $this->cleanup();

        $this->seedAdminsAndRoles();
        $this->seedCustomers();
        $this->seedMoreProducts();
        $this->seedOrders();
        $this->seedOrderReturns();
        $this->seedReviews();
        $this->seedTransactions();
        $this->seedWithdrawals();
        $this->seedWarehouseStocks();
        $this->seedStockTransfers();
        $this->seedMoreArticles();
        $this->seedFavorites();

        $this->command->info('野径户外扩展演示数据写入完成！');
    }

    private function cleanup(): void
    {
        DB::table('customer_favorites')->truncate();
        DB::table('reviews')->truncate();
        DB::table('order_return_histories')->truncate();
        DB::table('order_returns')->truncate();
        DB::table('order_histories')->truncate();
        DB::table('order_shipments')->truncate();
        DB::table('order_payments')->truncate();
        DB::table('order_fees')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('customer_withdrawals')->truncate();
        DB::table('customer_transactions')->truncate();
        DB::table('stock_transfer_items')->truncate();
        DB::table('stock_transfers')->truncate();
        DB::table('warehouse_stock_movements')->truncate();
        DB::table('warehouse_stocks')->truncate();
        DB::table('addresses')->truncate();

        // 仅清理扩展文章，保留 ArticleSeeder 基础 4 篇
        $extraArticleIds = DB::table('articles')->whereIn('slug', $this->extraArticleSlugs)->pluck('id');
        if ($extraArticleIds->isNotEmpty()) {
            DB::table('article_tags')->whereIn('article_id', $extraArticleIds)->delete();
            DB::table('article_translations')->whereIn('article_id', $extraArticleIds)->delete();
            DB::table('articles')->whereIn('id', $extraArticleIds)->delete();
        }

        // 清理扩展商品（SPU 前缀 WP-）
        $extraProductIds = DB::table('products')->where('spu_code', 'like', 'WP-%')->pluck('id');
        if ($extraProductIds->isNotEmpty()) {
            DB::table('product_categories')->whereIn('product_id', $extraProductIds)->delete();
            DB::table('product_skus')->whereIn('product_id', $extraProductIds)->delete();
            DB::table('product_translations')->whereIn('product_id', $extraProductIds)->delete();
            DB::table('products')->whereIn('id', $extraProductIds)->delete();
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('customers')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            DB::table('customers')->delete();
        }
    }

    private function seedAdminsAndRoles(): void
    {
        $this->command->info('写入管理员与角色...');

        $roleTable = config('permission.table_names.roles', 'roles');
        $roles     = [
            ['name' => 'Super Admin', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gear Editor', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Order Manager', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Warehouse Staff', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table($roleTable)->insertOrIgnore($roles);

        $admins = [
            ['name' => '装备编辑·小陈', 'email' => 'editor@wildpath.demo', 'password' => Hash::make(self::DEMO_PASSWORD), 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '订单主管·老刘', 'email' => 'order@wildpath.demo', 'password' => Hash::make(self::DEMO_PASSWORD), 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '仓储管理·小王', 'email' => 'warehouse@wildpath.demo', 'password' => Hash::make(self::DEMO_PASSWORD), 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('admins')->insertOrIgnore($admins);
    }

    private function seedCustomers(): void
    {
        $this->command->info('写入户外爱好者客户...');

        $names = [
            '林峰', '陈雨薇', '赵远征', '孙晓晨', '周子墨', '吴思远', '郑雅文', '黄天翔',
            '刘畅', '张沐阳', '何静', '马腾', '徐野', '韩雪', '罗行', '梁野',
            'Alex Turner', 'Emma Walsh', 'Ryan Chen', 'Sophie Park', 'Marcus Lee',
            'Olivia Grant', 'Daniel Wu', 'Mia Thompson', 'James Liu', 'Charlotte Kim',
            'Henry Zhang', 'Ava Martinez', 'Noah Singh', 'Isabella Rossi',
        ];
        $froms    = ['pc_web', 'mobile_web', 'miniapp', 'app'];
        $groupIds = DB::table('customer_groups')->pluck('id')->toArray();

        $customers = [];
        foreach ($names as $i => $name) {
            $slug        = Str::slug($name) ?: 'user';
            $createdAt   = Carbon::now()->subDays(rand(1, 180))->subHours(rand(0, 23));
            $customers[] = [
                'name'              => $name,
                'email'             => $slug.($i > 0 ? $i : '').'@wildpath.demo',
                'password'          => Hash::make(self::DEMO_PASSWORD),
                'customer_group_id' => $groupIds[array_rand($groupIds)] ?? 1,
                'from'              => $froms[array_rand($froms)],
                'locale'            => $this->locales[array_rand($this->locales)],
                'active'            => rand(0, 9) > 0 ? 1 : 0,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ];
        }
        DB::table('customers')->insert($customers);

        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $stateIdList = DB::table('states')->pluck('id')->toArray();
        $stateNames  = DB::table('states')->pluck('name', 'id')->toArray();
        $countryIds  = DB::table('countries')->where('active', 1)->pluck('id')->toArray();
        $cities      = ['成都', '北京', '上海', '广州', '杭州', '昆明', '拉萨', '西安', '深圳', '重庆'];
        $streets     = ['户外大道', '探险路', '露营街', '徒步巷', '登山环路', '野营里', 'Trail Ave', 'Summit Rd'];

        $addresses = [];
        foreach ($customerIds as $cid) {
            $numAddr = rand(1, 2);
            for ($j = 0; $j < $numAddr; $j++) {
                $sid         = $stateIdList ? $stateIdList[array_rand($stateIdList)] : 0;
                $addresses[] = [
                    'customer_id' => $cid,
                    'guest_id'    => 0,
                    'name'        => $names[array_rand($names)],
                    'email'       => 'addr'.$cid.'@wildpath.demo',
                    'phone'       => '+86'.rand(13000000000, 19999999999),
                    'country_id'  => $countryIds ? $countryIds[array_rand($countryIds)] : 1,
                    'state_id'    => $sid,
                    'state'       => $stateNames[$sid] ?? '',
                    'city'        => $cities[array_rand($cities)],
                    'zipcode'     => str_pad(rand(100000, 999999), 6, '0'),
                    'address_1'   => rand(1, 999).'号'.$streets[array_rand($streets)],
                    'address_2'   => '',
                    'default'     => $j === 0 ? 1 : 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }
        DB::table('addresses')->insert($addresses);
    }

    private function seedMoreProducts(): void
    {
        $this->command->info('写入扩展户外商品...');

        $productData = [
            ['name_en' => 'Ultralight Canister Stove 120g', 'name_zh' => '超轻气罐炉头 120g', 'price' => 45.00, 'weight' => 0.12],
            ['name_en' => 'Gravity Water Filter 2L', 'name_zh' => '重力净水袋 2L', 'price' => 38.00, 'weight' => 0.08],
            ['name_en' => 'Insulated Hydration Bladder 3L', 'name_zh' => '保温水袋 3L', 'price' => 32.00, 'weight' => 0.15],
            ['name_en' => 'Silicone Foldable Bowl Set', 'name_zh' => '硅胶折叠碗套装', 'price' => 22.00, 'weight' => 0.06],
            ['name_en' => 'Emergency Bivy Sack', 'name_zh' => '应急保命睡袋', 'price' => 28.00, 'weight' => 0.18],
            ['name_en' => 'Trekking Gaiters Waterproof', 'name_zh' => '防水雪套', 'price' => 35.00, 'weight' => 0.14],
            ['name_en' => 'Camping Hammock with Mosquito Net', 'name_zh' => '带蚊帐露营吊床', 'price' => 55.00, 'weight' => 0.65],
            ['name_en' => 'Portable Camp Chair 600g', 'name_zh' => '便携折叠椅 600g', 'price' => 68.00, 'weight' => 0.6],
            ['name_en' => 'Dry Bag Set 3-Pack IPX7', 'name_zh' => '防水收纳袋三件套', 'price' => 26.00, 'weight' => 0.1],
            ['name_en' => 'Magnesium Fire Starter Kit', 'name_zh' => '镁棒点火套装', 'price' => 15.00, 'weight' => 0.05],
            ['name_en' => 'GPS Handheld Navigator', 'name_zh' => '手持 GPS 导航仪', 'price' => 189.00, 'weight' => 0.21],
            ['name_en' => 'Soft Shell Climbing Pants', 'name_zh' => '软壳攀岩裤', 'price' => 98.00, 'weight' => 0.38],
            ['name_en' => 'Merino Wool Base Layer', 'name_zh' => '美利奴羊毛打底', 'price' => 79.00, 'weight' => 0.16],
            ['name_en' => 'Bike Frame Bag 4L', 'name_zh' => '自行车车架包 4L', 'price' => 42.00, 'weight' => 0.12],
            ['name_en' => 'Solar Panel Charger 28W', 'name_zh' => '太阳能充电板 28W', 'price' => 119.00, 'weight' => 0.72],
            ['name_en' => 'Climbing Chalk Bag', 'name_zh' => '攀岩镁粉袋', 'price' => 18.00, 'weight' => 0.08],
            ['name_en' => 'Portable Shower Bag 20L', 'name_zh' => '便携淋浴袋 20L', 'price' => 24.00, 'weight' => 0.2],
            ['name_en' => 'Trekking Pole Tip Protectors', 'name_zh' => '登山杖脚套保护套', 'price' => 12.00, 'weight' => 0.03],
            ['name_en' => 'Camping First Aid Kit Pro', 'name_zh' => '户外专业急救包', 'price' => 49.00, 'weight' => 0.35],
            ['name_en' => 'Inflatable Sleeping Pad R-Value 4.2', 'name_zh' => '充气防潮垫 R值4.2', 'price' => 88.00, 'weight' => 0.48],
        ];

        $brandIds       = DB::table('brands')->pluck('id')->toArray();
        $categoryIds    = DB::table('categories')->pluck('id')->toArray();
        $taxClassIds    = DB::table('tax_classes')->pluck('id')->toArray();
        $weightClassIds = DB::table('weight_classes')->pluck('id')->toArray();

        foreach ($productData as $idx => $p) {
            $createdAt = Carbon::now()->subDays(rand(1, 120));
            $spuCode   = 'WP-'.str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $slug      = Str::slug($p['name_en']);

            $productId = DB::table('products')->insertGetId([
                'brand_id'     => $brandIds ? $brandIds[array_rand($brandIds)] : 0,
                'tax_class_id' => $taxClassIds ? $taxClassIds[array_rand($taxClassIds)] : 0,
                'weight_class' => $weightClassIds ? $weightClassIds[array_rand($weightClassIds)] : 0,
                'spu_code'     => $spuCode,
                'slug'         => $slug,
                'price'        => $p['price'],
                'weight'       => $p['weight'],
                'active'       => 1,
                'position'     => $idx + 100,
                'created_at'   => $createdAt,
                'updated_at'   => $createdAt,
            ]);

            foreach ($this->locales as $locale) {
                $name = $locale === 'en' ? $p['name_en'] : $p['name_zh'];
                DB::table('product_translations')->insert([
                    'product_id'       => $productId,
                    'locale'           => $locale,
                    'name'             => $name,
                    'summary'          => "WildPath 精选户外装备 — {$name}",
                    'content'          => "<p>{$name}，经 WildPath 户外测试团队实地验证，适合露营、徒步、攀岩等多种户外场景。</p>",
                    'meta_title'       => $name.' | WildPath',
                    'meta_description' => "购买 {$name}，WildPath 野径户外正品保障",
                ]);
            }

            $skuCode = $spuCode.'-SKU';
            DB::table('product_skus')->insert([
                'product_id'   => $productId,
                'code'         => $skuCode,
                'model'        => $skuCode,
                'price'        => $p['price'],
                'origin_price' => round($p['price'] * 1.25, 2),
                'quantity'     => rand(15, 300),
                'is_default'   => 1,
                'position'     => 0,
                'created_at'   => $createdAt,
                'updated_at'   => $createdAt,
            ]);

            if ($categoryIds) {
                $numCats = rand(1, 3);
                $cats    = (array) array_rand(array_flip($categoryIds), min($numCats, count($categoryIds)));
                foreach ($cats as $catId) {
                    DB::table('product_categories')->insertOrIgnore([
                        'product_id'  => $productId,
                        'category_id' => $catId,
                    ]);
                }
            }
        }
    }

    private function seedOrders(): void
    {
        $this->command->info('写入订单数据...');

        $customerIds         = DB::table('customers')->pluck('id')->toArray();
        $skus                = DB::table('product_skus')->get();
        $productTranslations = DB::table('product_translations')->where('locale', 'en')->get()->keyBy('product_id');
        $statuses            = ['unpaid', 'paid', 'shipped', 'completed', 'cancelled'];
        $shippingMethods     = ['standard', 'express', 'free_shipping'];
        $countries           = ['China', 'United States', 'Australia', 'Japan', 'Germany'];
        $cities              = ['成都', '北京', '上海', '昆明', '拉萨', 'Los Angeles', 'Sydney', 'Tokyo'];

        for ($i = 0; $i < 80; $i++) {
            $customerId = $customerIds[array_rand($customerIds)];
            $customer   = DB::table('customers')->find($customerId);
            $address    = DB::table('addresses')->where('customer_id', $customerId)->where('default', 1)->first()
                ?? DB::table('addresses')->where('customer_id', $customerId)->first();
            $status      = $statuses[array_rand($statuses)];
            $createdAt   = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));
            $orderNumber = 'WP'.$createdAt->format('Ymd').str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $country     = $address?->country_id ? (DB::table('countries')->find($address->country_id)?->name ?? 'China') : $countries[array_rand($countries)];
            $city        = $address?->city ?? $cities[array_rand($cities)];
            $phone       = $address?->phone ?? '+86'.rand(13000000000, 19999999999);
            $addrLine    = $address?->address_1 ?? rand(1, 999).' 户外装备路';
            $zipcode     = $address?->zipcode ?? str_pad(rand(100000, 999999), 6, '0');
            $stateName   = $address?->state ?? '';
            $countryId   = $address?->country_id ?? 44;
            $stateId     = $address?->state_id ?? 0;
            $addressId   = $address?->id ?? 0;

            $numItems  = rand(1, 4);
            $orderSkus = $skus->random(min($numItems, $skus->count()));
            $subtotal  = 0;
            $itemRows  = [];

            foreach ($orderSkus as $sku) {
                $qty   = rand(1, 3);
                $price = $sku->price;
                $subtotal += $price * $qty;
                $trans      = $productTranslations[$sku->product_id] ?? null;
                $itemRows[] = [
                    'product_id'    => $sku->product_id,
                    'order_number'  => $orderNumber,
                    'product_sku'   => $sku->code,
                    'variant_label' => $sku->code,
                    'name'          => $trans?->name ?? 'Product '.$sku->product_id,
                    'image'         => $sku->image ?? 'images/placeholder.png',
                    'quantity'      => $qty,
                    'price'         => $price,
                    'item_type'     => 'normal',
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                ];
            }

            $shippingFee = rand(0, 1) ? round(rand(800, 2500) / 100, 2) : 0;
            $total       = round($subtotal + $shippingFee, 2);

            $orderId = DB::table('orders')->insertGetId([
                'number'                 => $orderNumber,
                'customer_id'            => $customerId,
                'customer_group_id'      => $customer->customer_group_id ?? 1,
                'shipping_address_id'    => $addressId,
                'billing_address_id'     => $addressId,
                'customer_name'          => $customer->name,
                'email'                  => $customer->email,
                'calling_code'           => 86,
                'telephone'              => $phone,
                'total'                  => $total,
                'locale'                 => 'en',
                'currency_code'          => 'USD',
                'currency_value'         => '1',
                'status'                 => $status,
                'shipping_method_code'   => $shippingMethods[array_rand($shippingMethods)],
                'shipping_method_name'   => 'WildPath Standard Shipping',
                'shipping_customer_name' => $customer->name,
                'shipping_calling_code'  => 86,
                'shipping_telephone'     => $phone,
                'shipping_country'       => $country,
                'shipping_country_id'    => $countryId,
                'shipping_state_id'      => $stateId,
                'shipping_state'         => $stateName,
                'shipping_city'          => $city,
                'shipping_address_1'     => $addrLine,
                'shipping_address_2'     => '',
                'shipping_zipcode'       => $zipcode,
                'billing_method_code'    => 'bank_transfer',
                'billing_method_name'    => 'Bank Transfer',
                'billing_customer_name'  => $customer->name,
                'billing_calling_code'   => 86,
                'billing_telephone'      => $phone,
                'billing_country'        => $country,
                'billing_country_id'     => $countryId,
                'billing_state_id'       => $stateId,
                'billing_state'          => $stateName,
                'billing_city'           => $city,
                'billing_address_1'      => $addrLine,
                'billing_address_2'      => '',
                'billing_zipcode'        => $zipcode,
                'ip'                     => rand(1, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(1, 255),
                'user_agent'             => 'WildPath Demo Seeder',
                'created_at'             => $createdAt,
                'updated_at'             => $createdAt,
            ]);

            foreach ($itemRows as &$item) {
                $item['order_id'] = $orderId;
            }
            DB::table('order_items')->insert($itemRows);

            $fees = [['code' => 'sub_total', 'value' => $subtotal, 'title' => 'Subtotal']];
            if ($shippingFee > 0) {
                $fees[] = ['code' => 'shipping', 'value' => $shippingFee, 'title' => 'Shipping'];
            }
            $fees[] = ['code' => 'total', 'value' => $total, 'title' => 'Total'];
            foreach ($fees as $fee) {
                DB::table('order_fees')->insert(array_merge($fee, [
                    'order_id'   => $orderId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));
            }

            if (in_array($status, ['paid', 'shipped', 'completed'])) {
                DB::table('order_payments')->insert([
                    'order_id'     => $orderId,
                    'charge_id'    => 'ch_'.Str::random(24),
                    'amount'       => $total,
                    'handling_fee' => round($total * 0.029, 2),
                    'paid'         => $total,
                    'created_at'   => $createdAt->copy()->addMinutes(rand(1, 30)),
                    'updated_at'   => $createdAt->copy()->addMinutes(rand(1, 30)),
                ]);
            }

            if (in_array($status, ['shipped', 'completed'])) {
                $warehouseIds = DB::table('warehouses')->pluck('id')->toArray();
                $warehouse    = $warehouseIds ? DB::table('warehouses')->find($warehouseIds[array_rand($warehouseIds)]) : null;
                $shippedAt    = $createdAt->copy()->addDays(rand(1, 3));
                DB::table('order_shipments')->insert([
                    'order_id'        => $orderId,
                    'warehouse_id'    => $warehouse?->id,
                    'warehouse_name'  => $warehouse?->name ?? '成都西部中心仓',
                    'express_code'    => ['sf', 'zt', 'ems', 'db'][array_rand([0, 1, 2, 3])],
                    'express_company' => ['顺丰速运', '中通快递', 'EMS', '德邦物流'][array_rand([0, 1, 2, 3])],
                    'express_number'  => 'SF'.strtoupper(Str::random(10)),
                    'status'          => $status === 'completed' ? 'delivered' : 'shipped',
                    'shipped_at'      => $shippedAt,
                    'delivered_at'    => $status === 'completed' ? $shippedAt->copy()->addDays(rand(2, 7)) : null,
                    'created_at'      => $shippedAt,
                    'updated_at'      => $shippedAt,
                ]);
            }

            $historyStatuses = ['unpaid'];
            if (in_array($status, ['paid', 'shipped', 'completed'])) {
                $historyStatuses[] = 'paid';
            }
            if (in_array($status, ['shipped', 'completed'])) {
                $historyStatuses[] = 'shipped';
            }
            if ($status === 'completed') {
                $historyStatuses[] = 'completed';
            }
            if ($status === 'cancelled') {
                $historyStatuses[] = 'cancelled';
            }

            $historyTime = $createdAt->copy();
            foreach ($historyStatuses as $hs) {
                DB::table('order_histories')->insert([
                    'order_id'   => $orderId,
                    'status'     => $hs,
                    'notify'     => 0,
                    'comment'    => "订单状态更新为 {$hs}",
                    'created_at' => $historyTime,
                    'updated_at' => $historyTime,
                ]);
                $historyTime = $historyTime->copy()->addHours(rand(1, 48));
            }
        }
    }

    private function seedOrderReturns(): void
    {
        $this->command->info('写入退货数据...');

        $completedOrders = DB::table('orders')->whereIn('status', ['completed', 'shipped'])->get();
        $reasonIds       = DB::table('return_reasons')->pluck('id')->toArray();
        $returnStatuses  = ['created', 'pending', 'refunded', 'returned', 'cancelled'];
        $comments        = ['帐篷拉链损坏', '睡袋温标不符', '行程取消不需要了', '与页面描述不一致', '运输造成外箱破损'];

        $count = min(15, $completedOrders->count());
        if ($count === 0) {
            return;
        }

        $selectedOrders = $completedOrders->random($count);

        foreach ($selectedOrders as $order) {
            $item = DB::table('order_items')->where('order_id', $order->id)->first();
            if (! $item) {
                continue;
            }

            $createdAt = Carbon::parse($order->created_at)->addDays(rand(3, 14));
            DB::table('order_returns')->insert([
                'customer_id'   => $order->customer_id,
                'order_id'      => $order->id,
                'order_item_id' => $item->id,
                'product_id'    => $item->product_id,
                'number'        => 'RT'.$createdAt->format('Ymd').str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'order_number'  => $order->number,
                'product_name'  => $item->name,
                'product_sku'   => $item->product_sku,
                'quantity'      => rand(1, $item->quantity),
                'reason_id'     => $reasonIds ? $reasonIds[array_rand($reasonIds)] : null,
                'comment'       => $comments[array_rand($comments)],
                'status'        => $returnStatuses[array_rand($returnStatuses)],
                'opened'        => rand(0, 1),
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ]);
        }
    }

    private function seedReviews(): void
    {
        $this->command->info('写入商品评价...');

        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $productIds  = DB::table('products')->pluck('id')->toArray();
        $comments    = [
            '四姑娘山实测，帐篷抗风性能出色，强烈推荐！',
            '背包背负系统很舒适，65L 走贡嘎大环线无压力。',
            '睡袋压缩体积超预期，-10°C 够用。',
            '头灯亮度足，续航也长，夜爬必备。',
            '钛锅轻到不可思议，单人露营完美。',
            '冲锋衣防水没问题，就是颜色比图片略深。',
            '登山杖碳纤维款很稳，就是价格小贵。',
            '48 小时加急真的到了，出发前及时收到，感谢！',
            'Excellent tent for alpine camping, stayed dry in heavy rain.',
            'Backpack fits well, great for multi-day treks.',
            'Sleeping bag is warm and packs small. Love it!',
            'Headlamp is bright enough for night hiking.',
            'Fast shipping before my Kailash trek. Perfect timing.',
            'Quality gear at reasonable prices. Will buy again.',
            'The stove boils water in 3 minutes. Very efficient.',
        ];

        for ($i = 0; $i < 60; $i++) {
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            DB::table('reviews')->insert([
                'customer_id' => $customerIds[array_rand($customerIds)],
                'product_id'  => $productIds[array_rand($productIds)],
                'rating'      => rand(3, 5),
                'content'     => $comments[array_rand($comments)],
                'like'        => rand(0, 50),
                'dislike'     => rand(0, 3),
                'active'      => rand(0, 4) > 0 ? 1 : 0,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);
        }
    }

    private function seedTransactions(): void
    {
        $this->command->info('写入客户钱包流水...');

        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $types       = ['recharge', 'withdraw', 'refund', 'consumption', 'commission'];

        foreach ($customerIds as $cid) {
            $balance = 0;
            $numTx   = rand(0, 8);
            for ($i = 0; $i < $numTx; $i++) {
                $type   = $types[array_rand($types)];
                $amount = round(rand(500, 50000) / 100, 2);
                if (in_array($type, ['withdraw', 'consumption'])) {
                    $amount = -$amount;
                }
                $balance += $amount;
                if ($balance < 0) {
                    $balance = 0;
                }

                DB::table('customer_transactions')->insert([
                    'customer_id' => $cid,
                    'amount'      => $amount,
                    'type'        => $type,
                    'balance'     => round($balance, 2),
                    'comment'     => match ($type) {
                        'recharge'    => '户外装备预充值',
                        'refund'      => '退货运费返还',
                        'consumption' => '购买露营装备',
                        'commission'  => '驴友推荐返利',
                        default       => '提现至银行卡',
                    },
                    'created_at' => Carbon::now()->subDays(rand(1, 120)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedWithdrawals(): void
    {
        $this->command->info('写入提现申请...');

        $customerIds  = DB::table('customers')->limit(15)->pluck('id')->toArray();
        $statuses     = ['pending', 'approved', 'rejected', 'paid'];
        $accountTypes = ['bank', 'alipay', 'wechat'];

        foreach ($customerIds as $cid) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            DB::table('customer_withdrawals')->insert([
                'customer_id'    => $cid,
                'amount'         => round(rand(1000, 50000) / 100, 2),
                'account_type'   => $accountTypes[array_rand($accountTypes)],
                'account_number' => Str::random(16),
                'bank_name'      => ['工商银行', '招商银行', '支付宝', '微信支付'][array_rand([0, 1, 2, 3])],
                'status'         => $statuses[array_rand($statuses)],
                'comment'        => '户外装备返利提现',
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ]);
        }
    }

    private function seedWarehouseStocks(): void
    {
        $this->command->info('写入多仓库存...');

        $warehouseIds = DB::table('warehouses')->pluck('id')->toArray();
        $skus         = DB::table('product_skus')->get();

        if (empty($warehouseIds)) {
            $this->command->warn('未找到仓库，跳过库存写入（请先运行 WarehouseSeeder）');

            return;
        }

        foreach ($warehouseIds as $wid) {
            foreach ($skus as $sku) {
                $qty      = rand(0, 200);
                $reserved = rand(0, min(20, $qty));
                DB::table('warehouse_stocks')->insertOrIgnore([
                    'warehouse_id'        => $wid,
                    'product_id'          => $sku->product_id,
                    'sku_id'              => $sku->id,
                    'sku_code'            => $sku->code,
                    'quantity'            => $qty,
                    'reserved_quantity'   => $reserved,
                    'low_stock_threshold' => rand(5, 20),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                if ($qty > 0) {
                    DB::table('warehouse_stock_movements')->insert([
                        'warehouse_id' => $wid,
                        'sku_code'     => $sku->code,
                        'quantity'     => $qty,
                        'type'         => 'inbound',
                        'note'         => 'WildPath 初始入库',
                        'created_at'   => Carbon::now()->subDays(rand(30, 90)),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }

    private function seedStockTransfers(): void
    {
        $this->command->info('写入库存调拨...');

        $warehouseIds = DB::table('warehouses')->pluck('id')->toArray();
        if (count($warehouseIds) < 2) {
            return;
        }

        $statuses = ['pending', 'in_transit', 'completed', 'cancelled'];
        $adminIds = DB::table('admins')->pluck('id')->toArray();
        $skus     = DB::table('product_skus')->limit(10)->get();

        for ($i = 0; $i < 10; $i++) {
            $from      = $warehouseIds[array_rand($warehouseIds)];
            $to        = collect($warehouseIds)->reject(fn ($id) => $id === $from)->random();
            $status    = $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            $transferId = DB::table('stock_transfers')->insertGetId([
                'number'            => 'TF'.$createdAt->format('Ymd').str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $from,
                'to_warehouse_id'   => $to,
                'status'            => $status,
                'note'              => '川藏线旺季备货调拨 #'.($i + 1),
                'admin_id'          => $adminIds ? $adminIds[array_rand($adminIds)] : null,
                'shipped_at'        => in_array($status, ['in_transit', 'completed']) ? $createdAt->copy()->addDay() : null,
                'completed_at'      => $status === 'completed' ? $createdAt->copy()->addDays(3) : null,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);

            $numItems     = rand(1, 4);
            $transferSkus = $skus->random(min($numItems, $skus->count()));
            foreach ($transferSkus as $sku) {
                $qty = rand(5, 50);
                DB::table('stock_transfer_items')->insert([
                    'stock_transfer_id' => $transferId,
                    'sku_code'          => $sku->code,
                    'quantity'          => $qty,
                    'received_quantity' => $status === 'completed' ? $qty : 0,
                    'created_at'        => $createdAt,
                    'updated_at'        => $createdAt,
                ]);
            }
        }
    }

    private function seedMoreArticles(): void
    {
        $this->command->info('写入扩展文章...');

        $catalogIds = DB::table('catalogs')->pluck('id')->toArray();
        $tagIds     = DB::table('tags')->pluck('id')->toArray();

        $articles = [
            ['slug' => 'winter-camping-essential-gear', 'en' => 'Winter Camping Essential Gear List', 'zh' => '冬季露营必备装备清单'],
            ['slug' => 'how-to-choose-sleeping-bag', 'en' => 'How to Choose a Sleeping Bag by Temperature Rating', 'zh' => '如何按温标选择睡袋'],
            ['slug' => 'leave-no-trace-principles', 'en' => 'Leave No Trace: 7 Principles for Campers', 'zh' => 'LNT 无痕露营七大原则'],
            ['slug' => 'kailash-trek-preparation', 'en' => 'Kailash Trek Preparation Guide', 'zh' => '冈仁波齐转山装备准备指南'],
            ['slug' => 'waterproof-jacket-comparison', 'en' => 'Waterproof Jacket Comparison: PU3000 vs PU5000', 'zh' => '冲锋衣防水指数对比：PU3000 vs PU5000'],
            ['slug' => 'headlamp-lumen-guide', 'en' => 'Headlamp Lumen Guide for Night Hiking', 'zh' => '夜爬头灯流明选购指南'],
            ['slug' => 'family-camping-first-timer', 'en' => 'Family Camping Guide for First-Timers', 'zh' => '亲子露营新手入门指南'],
            ['slug' => 'backpack-fitting-guide', 'en' => 'Backpack Fitting Guide: Find Your Perfect Size', 'zh' => '登山背包背负调节完全指南'],
        ];

        foreach ($articles as $idx => $a) {
            $createdAt = Carbon::now()->subDays(rand(1, 60));
            $articleId = DB::table('articles')->insertGetId([
                'catalog_id' => $catalogIds ? $catalogIds[array_rand($catalogIds)] : null,
                'slug'       => $a['slug'],
                'position'   => $idx + 10,
                'viewed'     => rand(50, 8000),
                'author'     => ['WildPath 户外学院', '资深驴友·老周', 'TrailFox 编辑部'][array_rand([0, 1, 2])],
                'active'     => 1,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($this->locales as $locale) {
                $title = $locale === 'en' ? $a['en'] : $a['zh'];
                DB::table('article_translations')->insert([
                    'article_id'       => $articleId,
                    'locale'           => $locale,
                    'title'            => $title,
                    'summary'          => "WildPath 户外指南：{$title}",
                    'content'          => "<p>{$title}</p><p>本文由 WildPath 户外测试团队撰写，基于真实野外经验，帮助驴友做出明智的装备与线路决策。</p>",
                    'meta_title'       => $title.' | WildPath',
                    'meta_description' => "阅读 WildPath 户外指南：{$title}",
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);
            }

            if ($tagIds) {
                $numTags = rand(1, min(3, count($tagIds)));
                $tags    = (array) array_rand(array_flip($tagIds), $numTags);
                foreach ($tags as $tagId) {
                    DB::table('article_tags')->insertOrIgnore([
                        'article_id' => $articleId,
                        'tag_id'     => $tagId,
                    ]);
                }
            }
        }
    }

    private function seedFavorites(): void
    {
        $this->command->info('写入收藏数据...');

        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $productIds  = DB::table('products')->pluck('id')->toArray();

        foreach ($customerIds as $cid) {
            $numFavs = rand(0, 6);
            if ($numFavs === 0 || empty($productIds)) {
                continue;
            }
            $favProducts = (array) array_rand(array_flip($productIds), min($numFavs, count($productIds)));
            foreach ($favProducts as $pid) {
                DB::table('customer_favorites')->insertOrIgnore([
                    'customer_id' => $cid,
                    'product_id'  => $pid,
                    'created_at'  => Carbon::now()->subDays(rand(1, 90)),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
