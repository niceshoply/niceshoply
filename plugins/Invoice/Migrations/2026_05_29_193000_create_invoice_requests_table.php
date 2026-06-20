<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_requests')) {
            return;
        }

        Schema::create('invoice_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('title_type', 16)->default('personal')->comment('personal/company');
            $table->string('title', 191)->default('');
            $table->string('tax_no', 64)->nullable()->comment('企业税号');
            $table->string('content', 191)->default('');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('email', 191)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->string('bank_account', 64)->nullable();
            $table->string('reg_address', 191)->nullable();
            $table->string('reg_phone', 32)->nullable();
            $table->string('status', 16)->default('pending')->comment('pending/issued/rejected');
            $table->string('invoice_no', 64)->nullable()->comment('开具后的发票号');
            $table->string('invoice_file', 500)->nullable()->comment('发票文件URL');
            $table->string('admin_remark', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_requests');
    }
};
