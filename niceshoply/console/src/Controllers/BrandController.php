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
use NiceShoply\Common\Models\Brand;
use NiceShoply\Common\Repositories\BrandRepo;
use NiceShoply\Console\Requests\BrandRequest;

class BrandController extends BaseController
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
            'criteria' => BrandRepo::getCriteria(),
            'brands'   => BrandRepo::getInstance()->list($filters),
        ];

        return nice_view('console::brands.index', $data);
    }

    /**
     * Brand creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Brand);
    }

    /**
     * @param  BrandRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(BrandRequest $request): RedirectResponse
    {
        try {
            $data  = $request->all();
            $brand = BrandRepo::getInstance()->create($data);

            return redirect(console_route('brands.index'))
                ->with('instance', $brand)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('brands.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Brand  $brand
     * @return mixed
     * @throws Exception
     */
    public function show(Brand $brand): mixed
    {
        return $this->form($brand);
    }

    /**
     * @param  Brand  $brand
     * @return mixed
     * @throws Exception
     */
    public function edit(Brand $brand): mixed
    {
        return $this->form($brand);
    }

    /**
     * @param  $brand
     * @return mixed
     * @throws Exception
     */
    public function form($brand): mixed
    {
        $data = [
            'brand' => $brand,
        ];

        return nice_view('console::brands.form', $data);
    }

    /**
     * @param  BrandRequest  $request
     * @param  Brand  $brand
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        try {
            $data = $request->all();
            BrandRepo::getInstance()->update($brand, $data);

            return redirect(console_route('brands.index'))
                ->with('instance', $brand)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('brands.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Brand  $brand
     * @return RedirectResponse
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            BrandRepo::getInstance()->destroy($brand);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
