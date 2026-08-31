<?php

namespace App\Http\Controllers;

use App\Exceptions\RouteProviderException;
use App\Exceptions\TariffException;
use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\Tariff;
use App\Services\Delivery\DeliveryCalculationService;
use App\Services\Routes\RouteProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(private readonly DeliveryCalculationService $calculationService)
    {
    }

    public function create(): View
    {
        $user = request()->user();
        $calculation = null;

        if ($calculationId = session('calculation_id')) {
            $calculation = DeliveryCalculation::query()
                ->where('id', $calculationId)
                ->where('user_id', $user->id)
                ->first();
        }

        return view('delivery.create', [
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'recentCalculations' => $user->calculations()->latest()->limit(5)->get(),
            'activeTariff' => Tariff::query()->active()->withCount('zones')->first(),
            'provider' => app(RouteProvider::class),
            'calculation' => $calculation,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'customer_address' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $branch = Branch::query()->active()->findOrFail($validated['branch_id']);

        try {
            $calculation = $this->calculationService->calculate(
                $request->user(),
                $branch,
                $validated['customer_address'],
            );
        } catch (RouteProviderException|TariffException $exception) {
            return back()->withInput()->withErrors(['customer_address' => $exception->getMessage()]);
        }

        return redirect()
            ->route('delivery.create')
            ->with('calculation_id', $calculation->id)
            ->with('success', 'Расчёт сохранён в истории.');
    }
}
