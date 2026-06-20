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
use NiceShoply\Common\Models\Country;
use NiceShoply\Common\Models\State;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Repositories\WarehouseRepo;
use NiceShoply\Console\Requests\WarehouseRequest;

class WarehouseController extends BaseController
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
            'criteria'   => WarehouseRepo::getCriteria(),
            'warehouses' => WarehouseRepo::getInstance()->list($filters),
        ];

        return nice_view('console::warehouses.index', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Warehouse);
    }

    /**
     * @param  WarehouseRequest  $request
     * @return RedirectResponse
     */
    public function store(WarehouseRequest $request): RedirectResponse
    {
        try {
            $data      = $this->resolveLocationNames($request->all());
            $warehouse = WarehouseRepo::getInstance()->create($data);

            return redirect(console_route('warehouses.index'))
                ->with('instance', $warehouse)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('warehouses.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Warehouse  $warehouse
     * @return mixed
     * @throws Exception
     */
    public function edit(Warehouse $warehouse): mixed
    {
        return $this->form($warehouse);
    }

    /**
     * @param  $warehouse
     * @return mixed
     * @throws Exception
     */
    public function form($warehouse): mixed
    {
        if ($warehouse->id) {
            $warehouse->load('serviceAreas');
        }

        $data = [
            'warehouse' => $warehouse,
        ];

        return nice_view('console::warehouses.form', $data);
    }

    /**
     * @param  WarehouseRequest  $request
     * @param  Warehouse  $warehouse
     * @return RedirectResponse
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        try {
            $data = $this->resolveLocationNames($request->all());
            WarehouseRepo::getInstance()->update($warehouse, $data);

            return redirect(console_route('warehouses.index'))
                ->with('instance', $warehouse)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('warehouses.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Warehouse  $warehouse
     * @return RedirectResponse
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        try {
            WarehouseRepo::getInstance()->destroy($warehouse);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @param  int  $id
     * @return mixed
     */
    public function active(Request $request, int $id): mixed
    {
        $warehouse         = Warehouse::query()->findOrFail($id);
        $warehouse->active = $request->get('status', ! $warehouse->active);
        $warehouse->save();

        return json_success(console_trans('common.updated_success'));
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected function resolveLocationNames(array $data): array
    {
        $nullableDefaults = [
            'description', 'contact_name', 'contact_phone',
            'city', 'address_1', 'address_2', 'zipcode',
        ];

        foreach ($nullableDefaults as $field) {
            if (! isset($data[$field]) || is_null($data[$field])) {
                $data[$field] = '';
            }
        }

        $data['is_default'] = ! empty($data['is_default']);
        $data['active']     = ! empty($data['active']);

        if (! empty($data['country_id'])) {
            $country = Country::query()->find($data['country_id']);
            if ($country) {
                $data['country'] = $country->name;
            }
        }

        if (! empty($data['state_id'])) {
            $state = State::query()->find($data['state_id']);
            if ($state) {
                $data['state'] = $state->name;
            }
        }

        return $data;
    }
}
