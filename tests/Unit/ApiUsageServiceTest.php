<?php

namespace Tests\Unit;

use App\Models\ApiUsageCounter;
use App\Services\Delivery\ApiUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUsageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_tracks_2gis_requests_and_calculates_remaining_quota(): void
    {
        $service = app(ApiUsageService::class);
        $counter = $service->counter(ApiUsageCounter::SERVICE_GEOCODER);
        $counter->update([
            'quota_limit' => 1000,
            'baseline_used' => 56,
            'requests_used' => 0,
        ]);

        $service->recordTwoGisRequest(ApiUsageCounter::SERVICE_GEOCODER);
        $service->recordTwoGisRequest(ApiUsageCounter::SERVICE_GEOCODER);

        $counter->refresh();

        $this->assertSame(58, $counter->totalUsed());
        $this->assertSame(942, $counter->remaining());
        $this->assertSame(6, $counter->usagePercent());
    }
}
