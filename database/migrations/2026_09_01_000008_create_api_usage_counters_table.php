<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_usage_counters', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('service');
            $table->unsignedInteger('quota_limit')->default(1000);
            $table->unsignedInteger('baseline_used')->default(0);
            $table->unsignedInteger('requests_used')->default(0);
            $table->date('period_ends_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_usage_counters');
    }
};
