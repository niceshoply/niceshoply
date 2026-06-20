<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Visit\Visit;
use NiceShoply\Common\Services\VisitEnrichService;
use NiceShoply\Common\Services\VisitStatisticsService;

/**
 * 访问追踪与分析后台 API 控制器
 *
 * 提供访问明细列表、按周期统计、转化漏斗数据。
 */
class VisitController extends BaseController
{
    /**
     * 访问明细列表（支持设备、地域、日期筛选）。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $perPage = (int) $request->get('per_page', 15);

        $builder = Visit::query()->with('customer')->orderByDesc('last_visited_at');

        if ($deviceType = $request->get('device_type')) {
            $builder->where('device_type', $deviceType);
        }

        if ($countryCode = $request->get('country_code')) {
            $builder->where('country_code', $countryCode);
        }

        if ($customerId = $request->get('customer_id')) {
            $builder->where('customer_id', (int) $customerId);
        }

        if ($start = $request->get('start_date')) {
            $builder->where('first_visited_at', '>=', Carbon::parse($start)->startOfDay());
        }

        if ($end = $request->get('end_date')) {
            $builder->where('first_visited_at', '<=', Carbon::parse($end)->endOfDay());
        }

        $visits = $builder->paginate($perPage);

        return read_json_success($visits);
    }

    /**
     * 按周期获取访问 + 转化统计。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function statistics(Request $request): mixed
    {
        $period = $request->get('period', 'day');
        $date   = $request->get('date') ? Carbon::parse($request->get('date')) : null;

        $stats = app(VisitStatisticsService::class)->getPeriodStatistics($period, $date);

        return read_json_success($stats);
    }

    /**
     * 手动触发某日聚合（运维/补数据用）。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function aggregate(Request $request): mixed
    {
        $date = $request->get('date');

        app(VisitStatisticsService::class)->aggregateDaily($date);

        return update_json_success(['date' => $date ?: Carbon::yesterday()->toDateString()]);
    }

    /**
     * 批量补全缺失的访问数据（地理位置 + 浏览器/系统）。
     *
     * 用于历史数据回填或 GeoIP 库更新后的重新富集，单次最多处理 500 条。
     *
     * @return mixed
     */
    public function enrich(): mixed
    {
        $result = app(VisitEnrichService::class)->batchLocate();

        return update_json_success($result);
    }
}
