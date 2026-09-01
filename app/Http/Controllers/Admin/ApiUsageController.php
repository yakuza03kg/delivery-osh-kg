<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageCounter;
use App\Services\Delivery\ApiUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiUsageController extends Controller
{
    public function __construct(private readonly ApiUsageService $apiUsageService)
    {
    }

    public function index(): View
    {
        return view('admin.api-usage.index', [
            'counters' => $this->apiUsageService->twoGisCounters()->keyBy('service'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'geocoder_quota_limit' => ['required', 'integer', 'min:1'],
            'geocoder_baseline_used' => ['required', 'integer', 'min:0'],
            'routing_quota_limit' => ['required', 'integer', 'min:1'],
            'routing_baseline_used' => ['required', 'integer', 'min:0'],
            'period_ends_at' => ['nullable', 'date'],
            'reset_local_counters' => ['nullable', 'boolean'],
        ]);

        foreach ([
            ApiUsageCounter::SERVICE_GEOCODER => 'geocoder',
            ApiUsageCounter::SERVICE_ROUTING => 'routing',
        ] as $service => $prefix) {
            $counter = $this->apiUsageService->counter($service);
            $counter->update([
                'quota_limit' => $validated["{$prefix}_quota_limit"],
                'baseline_used' => $validated["{$prefix}_baseline_used"],
                'period_ends_at' => $validated['period_ends_at'] ?? null,
                'last_synced_at' => now(),
                'requests_used' => $request->boolean('reset_local_counters') ? 0 : $counter->requests_used,
            ]);
        }

        return redirect()
            ->route('admin.api-usage.index')
            ->with('success', 'Лимиты 2GIS синхронизированы.');
    }
}
