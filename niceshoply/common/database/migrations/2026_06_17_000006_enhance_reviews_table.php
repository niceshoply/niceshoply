<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'images')) {
                $table->json('images')->nullable()->comment('晒图 URL 列表')->after('content');
            }
            if (! Schema::hasColumn('reviews', 'reply')) {
                $table->text('reply')->nullable()->comment('商家回复')->after('images');
            }
            if (! Schema::hasColumn('reviews', 'reply_at')) {
                $table->timestamp('reply_at')->nullable()->comment('商家回复时间')->after('reply');
            }
            if (! Schema::hasColumn('reviews', 'rating_dimensions')) {
                $table->json('rating_dimensions')->nullable()->comment('多维评分')->after('rating');
            }
            if (! Schema::hasColumn('reviews', 'status')) {
                $table->string('status', 16)->default('approved')->comment('pending|approved|rejected')->after('active');
                $table->index('status', 'reviews_status_idx');
            }
        });

        // 历史数据：按 active 映射审核状态
        if (Schema::hasColumn('reviews', 'status')) {
            DB::table('reviews')->where('active', false)->update(['status' => 'rejected']);
            DB::table('reviews')->where('active', true)->update(['status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'status')) {
                $table->dropIndex('reviews_status_idx');
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('reviews', 'rating_dimensions')) {
                $table->dropColumn('rating_dimensions');
            }
            if (Schema::hasColumn('reviews', 'reply_at')) {
                $table->dropColumn('reply_at');
            }
            if (Schema::hasColumn('reviews', 'reply')) {
                $table->dropColumn('reply');
            }
            if (Schema::hasColumn('reviews', 'images')) {
                $table->dropColumn('images');
            }
        });
    }
};
