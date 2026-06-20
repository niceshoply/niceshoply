<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Invoice\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Invoice\Models\InvoiceRequest;
use Plugin\Invoice\Services\InvoiceService;

class InvoiceController extends BaseController
{
    protected string $modelClass = InvoiceRequest::class;

    public function index(Request $request): mixed
    {
        $invoices = InvoiceRequest::query()
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return nice_view('Invoice::console.invoices', compact('invoices'));
    }

    public function issue(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate([
                'invoice_no'   => 'required|string|max:64',
                'invoice_file' => 'nullable|string|max:500',
                'admin_remark' => 'nullable|string|max:500',
            ]);

            InvoiceService::getInstance()->issue($id, $data['invoice_no'], $data['invoice_file'] ?? '', $data['admin_remark'] ?? '');

            return json_success(__('Invoice::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function reject(Request $request, int $id): mixed
    {
        try {
            $data = $request->validate([
                'admin_remark' => 'nullable|string|max:500',
            ]);

            InvoiceService::getInstance()->reject($id, $data['admin_remark'] ?? '');

            return json_success(__('Invoice::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
