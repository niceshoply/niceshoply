<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Invoice\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Invoice\Models\InvoiceRequest;
use Plugin\Invoice\Services\InvoiceService;

class InvoiceController extends BaseController
{
    public function index(): mixed
    {
        $list = InvoiceRequest::query()
            ->where('customer_id', (int) token_customer_id())
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $list);
    }

    public function show(int $id): mixed
    {
        $request = InvoiceRequest::query()
            ->where('customer_id', (int) token_customer_id())
            ->findOrFail($id);

        return json_success('ok', $request);
    }

    public function store(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'order_id'     => 'required|integer|min:1',
                'title_type'   => 'required|in:personal,company',
                'title'        => 'required|string|max:191',
                'tax_no'       => 'required_if:title_type,company|nullable|string|max:64',
                'content'      => 'nullable|string|max:191',
                'amount'       => 'nullable|numeric|min:0',
                'email'        => 'nullable|email|max:191',
                'phone'        => 'nullable|string|max:32',
                'bank_name'    => 'nullable|string|max:191',
                'bank_account' => 'nullable|string|max:64',
                'reg_address'  => 'nullable|string|max:191',
                'reg_phone'    => 'nullable|string|max:32',
            ]);

            $invoice = InvoiceService::getInstance()->create((int) token_customer_id(), $data);

            return json_success(__('Invoice::common.submitted'), ['number' => $invoice->number]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
