<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\VisitRepo;
use NiceShoply\Common\Services\GeoLocationService;
use NiceShoply\Common\Services\VisitEnrichService;
use NiceShoply\Common\Services\VisitStatisticsService;

/**
 * 后台访问追踪与分析控制器
 *
 * 提供访问明细列表（设备/地域/浏览器/客户筛选）、访问与转化漏斗统计，
 * 以及数据补全（GeoIP / 浏览器解析）与每日聚合的运维触发入口。
 */
class VisitController extends BaseController
{
    /**
     * 访问明细列表。
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $data = [
            'criteria'      => VisitRepo::getCriteria(),
            'visits'        => VisitRepo::getInstance()->list($filters),
            'geo_available' => (new GeoLocationService)->isAvailable(),
        ];

        return nice_view('console::visits.index', $data);
    }

    /**
     * 访问与转化统计页。
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function statistics(Request $request): mixed
    {
        $period = $request->get('period', 'day');
        if (! in_array($period, ['day', 'week', 'month', 'year'], true)) {
            $period = 'day';
        }

        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::now();

        $stats = app(VisitStatisticsService::class)->getPeriodStatistics($period, $date);

        $data = [
            'period' => $period,
            'date'   => $date->toDateString(),
            'stats'  => $stats,
        ];

        return nice_view('console::visits.statistics', $data);
    }

    /**
     * 批量补全缺失的访问数据（GeoIP 地理位置 + 浏览器/系统），单次最多 500 条。
     *
     * @return mixed
     */
    public function enrich(): mixed
    {
        try {
            $result = app(VisitEnrichService::class)->batchLocate();

            return json_success(
                trans('console/visit.enrich_success', ['count' => $result['updated'] ?? 0]),
                $result
            );
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 手动触发指定日期（默认昨日）的访问与转化每日聚合。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function aggregate(Request $request): mixed
    {
        try {
            $date = $request->get('date');

            app(VisitStatisticsService::class)->aggregateDaily($date);

            return json_success(trans('console/visit.aggregate_success'), [
                'date' => $date ?: Carbon::yesterday()->toDateString(),
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
