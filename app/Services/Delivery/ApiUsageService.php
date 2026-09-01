<?php

namespace App\Services\Delivery;

use App\Models\ApiUsageCounter;
use Illuminate\Support\Collection;
use Throwable;

final class ApiUsageService
{
    public function recordTwoGisRequest(string $service): void
    {
        try {
            $counter = $this->counter($service);
            $counter->increment('requests_used');
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function twoGisCounters(): Collection
    {
        return collect([
            $this->counter(ApiUsageCounter::SERVICE_GEOCODER),
            $this->counter(ApiUsageCounter::SERVICE_ROUTING),
        ]);
    }

    public function counter(string $service): ApiUsageCounter
    {
        return ApiUsageCounter::query()->firstOrCreate(
            [
                'provider' => ApiUsageCounter::PROVIDER_TWO_GIS,
                'service' => $service,
            ],
            $this->defaults($service),
        );
    }

    private function defaults(string $service): array
    {
        $prefix = $service === ApiUsageCounter::SERVICE_ROUTING ? 'routing' : 'geocoder';

        return [
            'quota_limit' => (int) config("delivery.two_gis.{$prefix}_limit", 1000),
            'baseline_used' => (int) config("delivery.two_gis.{$prefix}_baseline", 0),
            'period_ends_at' => config('delivery.two_gis.period_ends_at'),
            'last_synced_at' => now(),
        ];
    }
}
