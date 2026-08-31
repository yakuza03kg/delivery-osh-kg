<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_caches', function (Blueprint $table): void {
            $table->id();
            $table->string('route_hash', 64)->unique();
            $table->decimal('origin_latitude', 10, 7);
            $table->decimal('origin_longitude', 10, 7);
            $table->decimal('destination_latitude', 10, 7);
            $table->decimal('destination_longitude', 10, 7);
            $table->unsignedInteger('distance_meters');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->longText('geometry')->nullable();
            $table->string('provider');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_caches');
    }
};
