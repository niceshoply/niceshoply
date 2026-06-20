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
use NiceShoply\Common\Models\Region;
use NiceShoply\Common\Repositories\CountryRepo;
use NiceShoply\Common\Repositories\RegionRepo;
use NiceShoply\Console\Requests\RegionRequest;
use Throwable;

class RegionController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => RegionRepo::getCriteria(),
            'regions'  => RegionRepo::getInstance()->list($filters),
        ];

        return nice_view('console::regions.index', $data);
    }

    /**
     * @param  Region  $region
     * @return Region
     */
    public function show(Region $region): Region
    {
        return $region->load(['regionStates']);
    }

    /**
     * Region creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Region);
    }

    /**
     * @param  RegionRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(RegionRequest $request): mixed
    {
        try {
            $data   = $request->all();
            $region = RegionRepo::getInstance()->create($data);

            return create_json_success($region);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Region  $region
     * @return mixed
     * @throws Exception
     */
    public function edit(Region $region): mixed
    {
        return $this->form($region);
    }

    /**
     * @param  $region
     * @return mixed
     * @throws Exception
     */
    public function form($region): mixed
    {
        $data = [
            'region'    => $region,
            'countries' => CountryRepo::getInstance()->builder()->get(),
        ];

        return nice_view('console::regions.form', $data);
    }

    /**
     * @param  RegionRequest  $request
     * @param  Region  $region
     * @return mixed
     */
    public function update(RegionRequest $request, Region $region): mixed
    {
        try {
            $data = $request->all();
            RegionRepo::getInstance()->update($region, $data);

            return json_success(console_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Region  $region
     * @return RedirectResponse
     */
    public function destroy(Region $region): RedirectResponse
    {
        try {
            RegionRepo::getInstance()->destroy($region);

            return redirect(console_route('regions.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('regions.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
