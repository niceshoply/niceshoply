<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Visit\Visit;
use NiceShoply\Common\Repositories\VisitRepo;
use Tests\TestCase;

/**
 * 访问追踪后台 Repo 测试（IMP-05）
 */
class VisitRepoTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 应按设备类型筛选访问明细。
     */
    public function test_visit_repo_filters_by_device_type(): void
    {
        Visit::query()->create([
            'session_id'       => 'sess-desktop-'.uniqid(),
            'ip_address'       => '127.0.0.1',
            'device_type'      => 'desktop',
            'first_visited_at' => now(),
            'last_visited_at'  => now(),
        ]);
        Visit::query()->create([
            'session_id'       => 'sess-mobile-'.uniqid(),
            'ip_address'       => '127.0.0.2',
            'device_type'      => 'mobile',
            'first_visited_at' => now(),
            'last_visited_at'  => now(),
        ]);

        $results = VisitRepo::getInstance()->builder(['device_type' => 'mobile'])->get();

        $this->assertTrue($results->every(fn ($visit) => $visit->device_type === 'mobile'));
    }

    /**
     * 筛选条件配置应包含设备类型与访问时间范围。
     */
    public function test_visit_repo_criteria_contains_expected_fields(): void
    {
        $names = array_column(VisitRepo::getCriteria(), 'name');

        $this->assertContains('device_type', $names);
        $this->assertContains('country_code', $names);
        $this->assertContains('first_visited_at', $names);
    }
}
