<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_im_messages', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('direction', 16)->default('in');
            $table->string('peer_id', 128)->index();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_im_messages');
    }
};
