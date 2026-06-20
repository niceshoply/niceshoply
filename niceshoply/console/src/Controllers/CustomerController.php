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
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Repositories\CustomerRepo;
use NiceShoply\Console\Requests\CustomerRequest;
use Throwable;

class CustomerController extends BaseController
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
            'criteria'  => CustomerRepo::getCriteria(),
            'customers' => CustomerRepo::getInstance()->list($filters),
        ];

        return nice_view('console::customers.index', $data);
    }

    /**
     * Customer creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Customer);
    }

    /**
     * @param  CustomerRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(CustomerRequest $request): RedirectResponse
    {
        try {
            $data     = $request->all();
            $customer = CustomerRepo::getInstance()->create($data);

            return redirect(console_route('customers.index'))
                ->with('instance', $customer)
                ->with('success', console_trans('common.saved_success'));
        } catch (Exception $e) {
            return redirect(console_route('customers.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Customer  $customer
     * @return mixed
     * @throws Exception
     */
    public function edit(Customer $customer): mixed
    {
        return $this->form($customer);
    }

    /**
     * @param  $customer
     * @return mixed
     * @throws Exception
     */
    public function form($customer): mixed
    {
        $data = CustomerRepo::getInstance()->getCustomerDetailData($customer);

        return nice_view('console::customers.form', $data);
    }

    /**
     * @param  CustomerRequest  $request
     * @param  Customer  $customer
     * @return RedirectResponse
     */
    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        try {
            $data = $request->all();
            CustomerRepo::getInstance()->update($customer, $data);

            return redirect(console_route('customers.index'))
                ->with('instance', $customer)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('customers.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Customer  $customer
     * @return RedirectResponse
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            CustomerRepo::getInstance()->destroy($customer);

            return redirect(console_route('customers.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(console_route('customers.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Customer  $customer
     * @return mixed
     * @throws Exception
     */
    public function loginFrontend(Customer $customer): mixed
    {
        session()->forget('front_api_token');
        auth()->guard('customer')->loginUsingId($customer->id);

        return redirect(account_route('index'));
    }
}
