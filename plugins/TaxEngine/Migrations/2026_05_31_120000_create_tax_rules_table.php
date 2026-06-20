<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_engine_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 8)->index();
            $table->string('region_code', 32)->nullable()->index();
            $table->string('name', 128);
            $table->string('tax_type', 32)->default('vat');
            $table->decimal('rate', 8, 4)->default(0);
            $table->boolean('include_in_price')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_engine_rules');
    }
};
