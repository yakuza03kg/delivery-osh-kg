<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TariffController extends Controller
{
    public function index(): View
    {
        return view('admin.tariffs.index', [
            'tariffs' => Tariff::query()->with('zones')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.tariffs.form', ['tariff' => new Tariff(['mode' => Tariff::MODE_PER_KM, 'rounding' => 'none'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data): void {
            $tariff = Tariff::query()->create($data['tariff']);
            $this->saveZones($tariff, $data['zones']);

            if ($tariff->is_active) {
                Tariff::query()->where('id', '!=', $tariff->id)->update(['is_active' => false]);
            }
        });

        return redirect()->route('admin.tariffs.index')->with('success', 'Тариф добавлен.');
    }

    public function edit(Tariff $tariff): View
    {
        $tariff->load('zones');

        return view('admin.tariffs.form', compact('tariff'));
    }

    public function update(Request $request, Tariff $tariff): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $tariff): void {
            $tariff->update($data['tariff']);
            $tariff->zones()->delete();
            $this->saveZones($tariff, $data['zones']);

            if ($tariff->is_active) {
                Tariff::query()->where('id', '!=', $tariff->id)->update(['is_active' => false]);
            }
        });

        return redirect()->route('admin.tariffs.index')->with('success', 'Тариф обновлён.');
    }

    public function destroy(Tariff $tariff): RedirectResponse
    {
        $tariff->delete();

        return redirect()->route('admin.tariffs.index')->with('success', 'Тариф удалён.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mode' => ['required', 'in:per_km,progressive,zones'],
            'price_per_km' => ['nullable', 'numeric', 'min:0'],
            'base_km' => ['nullable', 'numeric', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'additional_price_per_km' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'rounding' => ['required', 'in:none,ceil,nearest,floor'],
            'zones' => ['nullable', 'array'],
            'zones.*.from_km' => ['nullable', 'numeric', 'min:0'],
            'zones.*.to_km' => ['nullable', 'numeric', 'min:0'],
            'zones.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $zones = collect($validated['zones'] ?? [])
            ->map(fn (array $zone): array => [
                'from_km' => $zone['from_km'] ?? null,
                'to_km' => $zone['to_km'] ?? null,
                'price' => $zone['price'] ?? null,
            ])
            ->filter(fn (array $zone): bool => $zone['from_km'] !== null || $zone['to_km'] !== null || $zone['price'] !== null)
            ->values();

        $messages = [];

        if ($validated['mode'] === Tariff::MODE_PER_KM && ($validated['price_per_km'] ?? null) === null) {
            $messages['price_per_km'] = 'Укажите стоимость за километр.';
        }

        if ($validated['mode'] === Tariff::MODE_PROGRESSIVE) {
            foreach (['base_km', 'base_price', 'additional_price_per_km'] as $field) {
                if (($validated[$field] ?? null) === null) {
                    $messages[$field] = 'Заполните все поля прогрессивного тарифа.';
                }
            }
        }

        if ($validated['mode'] === Tariff::MODE_ZONES && $zones->isEmpty()) {
            $messages['zones'] = 'Добавьте хотя бы одну тарифную зону.';
        }

        foreach ($zones as $zone) {
            if ($zone['from_km'] === null || $zone['price'] === null) {
                $messages['zones'] = 'Для каждой зоны укажите начало диапазона и цену.';
            }

            if ($zone['to_km'] !== null && (float) $zone['to_km'] < (float) $zone['from_km']) {
                $messages['zones'] = 'Конец зоны не может быть меньше её начала.';
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return [
            'tariff' => [
                'name' => $validated['name'],
                'mode' => $validated['mode'],
                'price_per_km' => $validated['price_per_km'] ?? null,
                'base_km' => $validated['base_km'] ?? 0,
                'base_price' => $validated['base_price'] ?? 0,
                'additional_price_per_km' => $validated['additional_price_per_km'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
                'rounding' => $validated['rounding'],
                'is_active' => $request->boolean('is_active'),
            ],
            'zones' => $zones->all(),
        ];
    }

    private function saveZones(Tariff $tariff, array $zones): void
    {
        foreach ($zones as $order => $zone) {
            $tariff->zones()->create([
                'from_km' => $zone['from_km'],
                'to_km' => $zone['to_km'],
                'price' => $zone['price'],
                'sort_order' => $order,
            ]);
        }
    }
}
