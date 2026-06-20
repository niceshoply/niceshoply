<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\ShippingZone;
use NiceShoply\Common\Repositories\ShippingZoneRepo;

/**
 * 配送区域后台控制器。
 */
class ShippingZoneController extends BaseController
{
    public function index(Request $request): mixed
    {
        $data = [
            'criteria' => ShippingZoneRepo::getCriteria(),
            'zones'    => ShippingZoneRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::shipping_zones.index', $data);
    }

    public function create(): mixed
    {
        return nice_view('console::shipping_zones.form', ['zone' => new ShippingZone]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            ShippingZoneRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('shipping_zones.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('shipping_zones.create'))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(ShippingZone $shippingZone): mixed
    {
        return nice_view('console::shipping_zones.form', ['zone' => $shippingZone]);
    }

    public function update(Request $request, ShippingZone $shippingZone): RedirectResponse
    {
        try {
            ShippingZoneRepo::getInstance()->update($shippingZone, $this->normalize($request));

            return redirect(console_route('shipping_zones.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('shipping_zones.edit', [$shippingZone->id]))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(ShippingZone $shippingZone): RedirectResponse
    {
        try {
            ShippingZoneRepo::getInstance()->destroy($shippingZone);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 把逗号分隔的国家/省州ID解析为数组。
     *
     * @param  Request  $request
     * @return array
     */
    private function normalize(Request $request): array
    {
        return [
            'name'        => $request->input('name', ''),
            'country_ids' => $this->parseIds($request->input('country_ids', '')),
            'state_ids'   => $this->parseIds($request->input('state_ids', '')),
            'priority'    => (int) $request->input('priority', 0),
            'active'      => (bool) $request->input('active', true),
        ];
    }

    /**
     * @param  mixed  $raw
     * @return array<int, int>
     */
    private function parseIds(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', preg_split('/[,\s]+/', $raw))));
    }
}
