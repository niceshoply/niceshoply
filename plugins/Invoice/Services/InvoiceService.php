<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Invoice\Services;

use Illuminate\Support\Str;
use Plugin\Invoice\Models\InvoiceRequest;

class InvoiceService
{
    public const STATUSES = ['pending', 'issued', 'rejected'];

    public static function getInstance(): static
    {
        return new static;
    }

    public function create(int $customerId, array $data): InvoiceRequest
    {
        $titleType = ($data['title_type'] ?? 'personal') === 'company' ? 'company' : 'personal';

        return InvoiceRequest::query()->create([
            'number'       => $this->generateNumber(),
            'order_id'     => (int) ($data['order_id'] ?? 0),
            'customer_id'  => $customerId,
            'title_type'   => $titleType,
            'title'        => $data['title'] ?? '',
            'tax_no'       => $titleType === 'company' ? ($data['tax_no'] ?? '') : null,
            'content'      => $data['content'] ?? plugin_setting('invoice', 'default_content', ''),
            'amount'       => (float) ($data['amount'] ?? 0),
            'email'        => $data['email'] ?? null,
            'phone'        => $data['phone'] ?? null,
            'bank_name'    => $data['bank_name'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'reg_address'  => $data['reg_address'] ?? null,
            'reg_phone'    => $data['reg_phone'] ?? null,
            'status'       => 'pending',
        ]);
    }

    protected function generateNumber(): string
    {
        do {
            $number = 'INV'.date('Ymd').strtoupper(Str::random(6));
        } while (InvoiceRequest::query()->where('number', $number)->exists());

        return $number;
    }

    /**
     * 后台开具发票（回填发票号 / 文件）。
     */
    public function issue(int $id, string $invoiceNo, string $invoiceFile = '', string $remark = ''): InvoiceRequest
    {
        $request = InvoiceRequest::query()->findOrFail($id);
        $request->status       = 'issued';
        $request->invoice_no   = $invoiceNo;
        $request->invoice_file = $invoiceFile ?: $request->invoice_file;
        if ($remark !== '') {
            $request->admin_remark = $remark;
        }
        $request->save();

        return $request;
    }

    public function reject(int $id, string $remark = ''): InvoiceRequest
    {
        $request = InvoiceRequest::query()->findOrFail($id);
        $request->status       = 'rejected';
        $request->admin_remark = $remark;
        $request->save();

        return $request;
    }
}
