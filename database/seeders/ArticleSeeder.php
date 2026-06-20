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
use NiceShoply\Common\Models\Article;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getArticles();
        if ($items) {
            Article::query()->truncate();
            foreach ($items as $item) {
                Article::query()->create($item);
            }
        }

        $items = $this->getArticleTranslations();
        if ($items) {
            Article\Translation::query()->truncate();
            foreach ($items as $item) {
                Article\Translation::query()->create($item);
            }
        }

        $items = $this->getArticleTags();
        if ($items) {
            Article\Tag::query()->truncate();
            foreach ($items as $item) {
                Article\Tag::query()->create($item);
            }
        }
    }

    private function getArticles(): array
    {
        return [
            [
                'id'         => 1,
                'catalog_id' => 1,
                'slug'       => 'alpine-storm-tent-field-test',
                'position'   => 0,
                'viewed'     => 328,
                'author'     => 'WildPath 评测组',
                'active'     => 1,
            ],
            [
                'id'         => 2,
                'catalog_id' => 1,
                'slug'       => 'ultralight-backpack-buying-guide',
                'position'   => 1,
                'viewed'     => 256,
                'author'     => 'TrailFox 编辑部',
                'active'     => 1,
            ],
            [
                'id'         => 3,
                'catalog_id' => 2,
                'slug'       => 'siguniang-west-ridge-guide',
                'position'   => 0,
                'viewed'     => 512,
                'author'     => '资深驴友·老周',
                'active'     => 1,
            ],
            [
                'id'         => 4,
                'catalog_id' => 2,
                'slug'       => 'spring-camping-checklist',
                'position'   => 1,
                'viewed'     => 189,
                'author'     => 'WildPath 户外学院',
                'active'     => 1,
            ],
        ];
    }

    private function getArticleTranslations(): array
    {
        return [
            [
                'article_id'       => 1,
                'locale'           => 'zh-cn',
                'title'            => 'Alpine Storm Pro 帐篷：四姑娘山二峰实测报告',
                'summary'          => '在海拔 4200m、夜间 -8°C、8 级阵风的极端条件下，Alpine Storm Pro 表现如何？',
                'image'            => 'images/demo/news/1.jpg',
                'content'          => '<p>我们在四姑娘山二峰大本营进行了为期 3 天的实地测试。Alpine Storm Pro 在暴雨与强风环境下帐内保持干燥，15 分钟完成搭建，双人款空间充裕。</p><p><strong>结论：</strong>适合高海拔三季至四季露营，性价比优于同价位进口品牌。</p>',
                'meta_title'       => 'Alpine Storm Pro 帐篷实测 | 野径户外',
                'meta_description' => '四姑娘山二峰实地测试 Alpine Storm Pro 四季帐篷',
                'meta_keywords'    => '帐篷评测,Alpine Storm,四姑娘山',
            ],
            [
                'article_id'       => 1,
                'locale'           => 'en',
                'title'            => 'Alpine Storm Pro Tent: Field Test on Siguniang West Ridge',
                'summary'          => 'How does the Alpine Storm Pro perform at 4200m, -8°C nights, and Force 8 gusts?',
                'image'            => 'images/demo/news/1.jpg',
                'content'          => '<p>We conducted a 3-day field test at Siguniang West Ridge basecamp. The Alpine Storm Pro stayed dry inside during heavy rain and strong winds, setup took 15 minutes, and the 2-person model offers ample space.</p><p><strong>Verdict:</strong> Excellent for alpine 3-4 season camping with better value than comparable imports.</p>',
                'meta_title'       => 'Alpine Storm Pro Field Test | WildPath',
                'meta_description' => 'Field test of Alpine Storm Pro tent on Siguniang West Ridge',
                'meta_keywords'    => 'tent review,Alpine Storm,Siguniang',
            ],
            [
                'article_id'       => 2,
                'locale'           => 'zh-cn',
                'title'            => '2026 轻量化背包选购指南：30L 到 65L 怎么选？',
                'summary'          => '一日徒步、周末露营、长线穿越——不同场景该选多大容量？',
                'image'            => 'images/demo/news/2.jpg',
                'content'          => '<p>30L 适合单日徒步；45L 覆盖 2-3 天露营；65L 及以上适合长线重装。重点关注背负系统、面料耐磨度与侧袋设计。</p>',
                'meta_title'       => '轻量化背包选购指南 | 野径户外',
                'meta_description' => '30L-65L 登山背包选购要点',
                'meta_keywords'    => '背包选购,轻量化,登山包',
            ],
            [
                'article_id'       => 2,
                'locale'           => 'en',
                'title'            => '2026 Ultralight Backpack Guide: 30L to 65L',
                'summary'          => 'Day hikes, weekend camping, thru-hikes — which capacity fits your trip?',
                'image'            => 'images/demo/news/2.jpg',
                'content'          => '<p>30L for day hikes; 45L for 2-3 day trips; 65L+ for expedition loads. Focus on suspension, fabric durability, and side pocket design.</p>',
                'meta_title'       => 'Ultralight Backpack Guide | WildPath',
                'meta_description' => 'How to choose 30L-65L hiking backpacks',
                'meta_keywords'    => 'backpack guide,ultralight,hiking',
            ],
            [
                'article_id'       => 3,
                'locale'           => 'zh-cn',
                'title'            => '四姑娘山西脊徒步攻略：3 天 2 夜经典线路',
                'summary'          => '从日隆镇出发，经锅庄坪、打包坪至大本营，完整装备清单与注意事项。',
                'image'            => 'images/demo/news/3.jpg',
                'content'          => '<p>Day 1：日隆 → 锅庄坪（海拔 3200m）；Day 2：锅庄坪 → 大本营（4200m）；Day 3：冲顶或下撤。必备：高海拔帐篷、-10°C 睡袋、头灯、保温层。</p>',
                'meta_title'       => '四姑娘山西脊攻略 | 野径户外',
                'meta_description' => '四姑娘山西脊 3 天 2 夜徒步完整攻略',
                'meta_keywords'    => '四姑娘山,徒步攻略,高海拔',
            ],
            [
                'article_id'       => 3,
                'locale'           => 'en',
                'title'            => 'Siguniang West Ridge: 3-Day Trek Guide',
                'summary'          => 'From Rilong Town via Guozhuangping to basecamp — gear list and safety tips.',
                'image'            => 'images/demo/news/3.jpg',
                'content'          => '<p>Day 1: Rilong → Guozhuangping (3200m); Day 2: Guozhuangping → Basecamp (4200m); Day 3: Summit or descent. Essentials: alpine tent, -10°C bag, headlamp, insulation layer.</p>',
                'meta_title'       => 'Siguniang West Ridge Guide | WildPath',
                'meta_description' => 'Complete 3-day trek guide for Siguniang West Ridge',
                'meta_keywords'    => 'Siguniang,trek guide,alpine',
            ],
            [
                'article_id'       => 4,
                'locale'           => 'zh-cn',
                'title'            => '春季露营必备清单：20 件装备一次备齐',
                'summary'          => '帐篷、睡袋、炉具、头灯……春季露营最容易遗漏的 5 件装备。',
                'image'            => 'images/demo/news/4.jpg',
                'content'          => '<p>春季昼夜温差大，务必携带保暖层与防潮垫。容易被忽略的：修补包、备用电池、垃圾袋（Leave No Trace 原则）。</p>',
                'meta_title'       => '春季露营清单 | 野径户外',
                'meta_description' => '春季露营 20 件必备装备清单',
                'meta_keywords'    => '春季露营,装备清单,Leave No Trace',
            ],
            [
                'article_id'       => 4,
                'locale'           => 'en',
                'title'            => 'Spring Camping Checklist: 20 Essential Items',
                'summary'          => 'Tent, sleeping bag, stove, headlamp — 5 items campers most often forget.',
                'image'            => 'images/demo/news/4.jpg',
                'content'          => '<p>Spring brings big temperature swings — pack insulation and a sleeping pad. Often forgotten: repair kit, spare batteries, trash bags (Leave No Trace).</p>',
                'meta_title'       => 'Spring Camping Checklist | WildPath',
                'meta_description' => '20 essential items for spring camping',
                'meta_keywords'    => 'spring camping,checklist,Leave No Trace',
            ],
        ];
    }

    private function getArticleTags(): array
    {
        return [
            ['id' => 1, 'article_id' => 1, 'tag_id' => 2],
            ['id' => 2, 'article_id' => 1, 'tag_id' => 3],
            ['id' => 3, 'article_id' => 2, 'tag_id' => 1],
            ['id' => 4, 'article_id' => 2, 'tag_id' => 3],
            ['id' => 5, 'article_id' => 3, 'tag_id' => 1],
            ['id' => 6, 'article_id' => 4, 'tag_id' => 2],
        ];
    }
}
