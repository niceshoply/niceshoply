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
use NiceShoply\Common\Models\TaxClass;
use NiceShoply\Common\Repositories\AddressRepo;
use NiceShoply\Common\Repositories\TaxClassRepo;
use NiceShoply\Common\Repositories\TaxRateRepo;
use NiceShoply\Console\Requests\TaxClassRequest;
use Throwable;

class TaxClassController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters  = $request->all();
        $taxRates = TaxRateRepo::getInstance()->all();

        $data = [
            'criteria'      => TaxClassRepo::getCriteria(),
            'tax_classes'   => TaxClassRepo::getInstance()->list($filters),
            'tax_rates'     => $taxRates,
            'address_types' => AddressRepo::getAddressTypes(),
        ];

        return nice_view('console::tax_classes.index', $data);
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     * @throws Exception
     */
    public function show(TaxClass $taxClass): mixed
    {
        $taxClass->load(['taxRules']);

        return $taxClass;
    }

    /**
     * TaxClass creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new TaxClass);
    }

    /**
     * @param  TaxClassRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(TaxClassRequest $request): mixed
    {
        try {
            $data     = $request->all();
            $taxClass = TaxClassRepo::getInstance()->create($data);

            return create_json_success($taxClass);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     * @throws Exception
     */
    public function edit(TaxClass $taxClass): mixed
    {
        return $this->form($taxClass);
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     */
    public function form(TaxClass $taxClass): mixed
    {
        $taxRates = TaxRateRepo::getInstance()->all();
        $taxRules = $taxClass->taxRules()->get();
        $data     = [
            'tax_class'     => $taxClass,
            'tax_rates'     => $taxRates,
            'tax_rules'     => $taxRules,
            'address_types' => AddressRepo::getAddressTypes(),
        ];

        return nice_view('console::tax_classes.form', $data);
    }

    /**
     * @param  TaxClassRequest  $request
     * @param  TaxClass  $taxClass
     * @return mixed
     */
    public function update(TaxClassRequest $request, TaxClass $taxClass): mixed
    {
        try {
            $data     = $request->all();
            $taxClass = TaxClassRepo::getInstance()->update($taxClass, $data);

            return update_json_success($taxClass);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TaxClass  $taxClass
     * @return RedirectResponse
     */
    public function destroy(TaxClass $taxClass): RedirectResponse
    {
        try {
            TaxClassRepo::getInstance()->destroy($taxClass);

            return redirect(console_route('tax_classes.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('tax_classes.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
