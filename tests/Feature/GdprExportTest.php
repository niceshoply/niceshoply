<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Jobs\GdprExportJob;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\GdprRequest;
use NiceShoply\Common\Repositories\LegalDocumentRepo;
use NiceShoply\Common\Services\Compliance\GdprService;
use Tests\TestCase;

/**
 * GDPR 数据导出集成测试。
 */
class GdprExportTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'gdpr-'.uniqid().'@example.com',
            'password'          => bcrypt('secret'),
            'name'              => 'GDPR Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ])->refresh();
    }

    public function test_export_request_creates_job_and_zip_file(): void
    {
        $customer = $this->makeCustomer();

        $request = GdprService::getInstance()->requestExport($customer, '127.0.0.1');
        $this->assertSame(GdprRequest::TYPE_EXPORT, $request->type);
        $this->assertSame(GdprRequest::STATUS_PENDING, $request->status);

        (new GdprExportJob($request->id))->handle();

        $request->refresh();
        $this->assertSame(GdprRequest::STATUS_COMPLETED, $request->status);
        $this->assertNotEmpty($request->file_path);
        $this->assertFileExists(storage_path('app/'.$request->file_path));
    }

    public function test_legal_consent_is_recorded(): void
    {
        $customer = $this->makeCustomer();
        $doc      = LegalDocumentRepo::getInstance()->create([
            'type'              => 'privacy',
            'version'           => '2.0',
            'active'            => true,
            'require_reconsent' => true,
            'translations'      => [
                'zh-cn' => ['title' => '隐私政策', 'content' => '<p>内容</p>'],
            ],
        ]);

        $this->assertFalse(LegalDocumentRepo::getInstance()->hasConsented($customer->id, $doc));

        LegalDocumentRepo::getInstance()->recordConsent($customer->id, $doc, '127.0.0.1', 'PHPUnit');

        $this->assertTrue(LegalDocumentRepo::getInstance()->hasConsented($customer->id, $doc));
    }
}
