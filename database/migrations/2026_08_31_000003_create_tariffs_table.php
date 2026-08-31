<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('mode')->default('per_km');
            $table->decimal('price_per_km', 10, 2)->nullable();
            $table->decimal('base_km', 10, 2)->default(0);
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('additional_price_per_km', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->string('rounding')->default('none');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
