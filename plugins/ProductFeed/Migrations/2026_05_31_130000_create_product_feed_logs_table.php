<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_feed_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('format', 16)->default('xml');
            $table->string('file_path', 512);
            $table->unsignedInteger('item_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_feed_logs');
    }
};
