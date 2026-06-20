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
        if (! Schema::hasTable('product_questions')) {
            Schema::create('product_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->string('content', 500);
                $table->unsignedInteger('answer_count')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->string('status', 16)->default('pending')->comment('pending/approved/rejected');
                $table->timestamps();

                $table->index(['product_id', 'status']);
            });
        }

        if (! Schema::hasTable('product_answers')) {
            Schema::create('product_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('question_id')->index();
                $table->unsignedBigInteger('customer_id')->default(0);
                $table->boolean('is_merchant')->default(false);
                $table->string('content', 500);
                $table->string('status', 16)->default('pending')->comment('pending/approved/rejected');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_answers');
        Schema::dropIfExists('product_questions');
    }
};
