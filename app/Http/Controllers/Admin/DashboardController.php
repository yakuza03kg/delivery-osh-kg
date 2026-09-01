<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\Tariff;
use App\Models\User;
use App\Services\Delivery\ApiUsageService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ApiUsageService $apiUsageService)
    {
    }

    public function index(): View
    {
        $today = now(config('delivery.timezone'))->startOfDay()->utc();

        return view('admin.dashboard', [
            'todayCalculations' => DeliveryCalculation::query()->where('created_at', '>=', $today)->count(),
            'todayRevenue' => DeliveryCalculation::query()->where('created_at', '>=', $today)->sum('price'),
            'couriersCount' => User::query()->where('role', 'courier')->count(),
            'branchesCount' => Branch::query()->active()->count(),
            'activeTariff' => Tariff::query()->active()->first(),
            'twoGisCounters' => $this->apiUsageService->twoGisCounters(),
            'recentCalculations' => DeliveryCalculation::query()->with(['branch', 'user'])->latest()->limit(8)->get(),
        ]);
    }
}
