<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_calculations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->nullOnDelete();
            $table->string('courier_name');
            $table->string('branch_name');
            $table->string('branch_address');
            $table->text('customer_address');
            $table->text('resolved_address')->nullable();
            $table->decimal('customer_latitude', 10, 7);
            $table->decimal('customer_longitude', 10, 7);
            $table->decimal('distance_km', 10, 2);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('KGS');
            $table->string('route_provider');
            $table->json('tariff_snapshot');
            $table->longText('route_geometry')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_calculations');
    }
};
