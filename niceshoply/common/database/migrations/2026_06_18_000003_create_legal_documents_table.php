<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nice_legal_documents')) {
            Schema::create('nice_legal_documents', function (Blueprint $table) {
                $table->comment('法律文档（隐私政策/服务条款等）');
                $table->id();
                $table->string('type', 32)->index('nice_legal_documents_type_idx')->comment('privacy|terms|cookie');
                $table->string('version', 32)->default('1.0')->comment('版本号，变更后需重新同意');
                $table->boolean('active')->default(true);
                $table->boolean('require_reconsent')->default(true)->comment('版本变更是否强制重新同意');
                $table->timestamps();

                $table->unique(['type', 'version'], 'nice_legal_documents_type_version_unique');
            });
        }

        if (! Schema::hasTable('nice_legal_document_translations')) {
            Schema::create('nice_legal_document_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legal_document_id')->index('nice_legal_doc_trans_doc_idx');
                $table->string('locale', 16);
                $table->string('title', 255)->default('');
                $table->longText('content')->nullable();
                $table->timestamps();

                $table->unique(['legal_document_id', 'locale'], 'nice_legal_doc_trans_unique');
            });
        }

        if (! Schema::hasTable('nice_customer_legal_consents')) {
            Schema::create('nice_customer_legal_consents', function (Blueprint $table) {
                $table->comment('客户法律文档同意记录');
                $table->id();
                $table->unsignedBigInteger('customer_id')->default(0)->index('nice_legal_consents_customer_idx');
                $table->unsignedBigInteger('legal_document_id')->index('nice_legal_consents_doc_idx');
                $table->string('document_version', 32);
                $table->string('document_type', 32);
                $table->string('ip', 64)->default('');
                $table->string('user_agent', 512)->default('');
                $table->timestamp('consented_at');
                $table->timestamps();

                $table->index(['customer_id', 'document_type'], 'nice_legal_consents_customer_type_idx');
            });
        }

        if (! Schema::hasTable('nice_gdpr_requests')) {
            Schema::create('nice_gdpr_requests', function (Blueprint $table) {
                $table->comment('GDPR 数据导出/删除申请');
                $table->id();
                $table->unsignedBigInteger('customer_id')->index('nice_gdpr_requests_customer_idx');
                $table->string('type', 16)->comment('export|delete');
                $table->string('status', 16)->default('pending')->comment('pending|processing|completed|failed');
                $table->string('file_path', 512)->default('');
                $table->text('error_message')->nullable();
                $table->string('ip', 64)->default('');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nice_customer_login_logs')) {
            Schema::create('nice_customer_login_logs', function (Blueprint $table) {
                $table->comment('客户登录日志（异常登录检测）');
                $table->id();
                $table->unsignedBigInteger('customer_id')->index('nice_login_logs_customer_idx');
                $table->string('ip', 64)->default('');
                $table->string('user_agent', 512)->default('');
                $table->boolean('success')->default(true);
                $table->string('failure_reason', 255)->default('');
                $table->boolean('is_new_device')->default(false);
                $table->timestamps();

                $table->index(['customer_id', 'created_at'], 'nice_login_logs_customer_time_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nice_customer_login_logs');
        Schema::dropIfExists('nice_gdpr_requests');
        Schema::dropIfExists('nice_customer_legal_consents');
        Schema::dropIfExists('nice_legal_document_translations');
        Schema::dropIfExists('nice_legal_documents');
    }
};
