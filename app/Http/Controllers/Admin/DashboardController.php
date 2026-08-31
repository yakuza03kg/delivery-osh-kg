<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();

        return view('admin.dashboard', [
            'todayCalculations' => DeliveryCalculation::query()->where('created_at', '>=', $today)->count(),
            'todayRevenue' => DeliveryCalculation::query()->where('created_at', '>=', $today)->sum('price'),
            'couriersCount' => User::query()->where('role', 'courier')->count(),
            'branchesCount' => Branch::query()->active()->count(),
            'activeTariff' => Tariff::query()->active()->first(),
            'recentCalculations' => DeliveryCalculation::query()->with(['branch', 'user'])->latest()->limit(8)->get(),
        ]);
    }
}
