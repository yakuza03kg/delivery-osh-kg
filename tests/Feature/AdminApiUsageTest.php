<?php

namespace Tests\Feature;

use App\Models\ApiUsageCounter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sync_2gis_usage_counters(): void
    {
        $admin = User::query()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.api-usage.update'), [
            'geocoder_quota_limit' => 1000,
            'geocoder_baseline_used' => 56,
            'routing_quota_limit' => 1000,
            'routing_baseline_used' => 22,
            'period_ends_at' => '2026-09-30',
            'reset_local_counters' => true,
        ]);

        $response->assertRedirect(route('admin.api-usage.index'));
        $this->assertDatabaseHas('api_usage_counters', [
            'provider' => ApiUsageCounter::PROVIDER_TWO_GIS,
            'service' => ApiUsageCounter::SERVICE_GEOCODER,
            'quota_limit' => 1000,
            'baseline_used' => 56,
            'requests_used' => 0,
        ]);
        $this->assertDatabaseHas('api_usage_counters', [
            'provider' => ApiUsageCounter::PROVIDER_TWO_GIS,
            'service' => ApiUsageCounter::SERVICE_ROUTING,
            'quota_limit' => 1000,
            'baseline_used' => 22,
            'requests_used' => 0,
        ]);
    }

    public function test_regular_admin_can_view_but_cannot_change_2gis_counters(): void
    {
        $admin = User::query()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.api-usage.index'))
            ->assertOk()
            ->assertSee('Синхронизация ограничена');

        $this->actingAs($admin)
            ->put(route('admin.api-usage.update'), [
                'geocoder_quota_limit' => 1,
                'geocoder_baseline_used' => 0,
                'routing_quota_limit' => 1,
                'routing_baseline_used' => 0,
            ])
            ->assertForbidden();
    }
}
