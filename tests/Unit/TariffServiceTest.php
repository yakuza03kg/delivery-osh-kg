<?php

namespace Tests\Unit;

use App\Exceptions\TariffException;
use App\Models\Tariff;
use App\Models\TariffZone;
use App\Services\Delivery\TariffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TariffServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_progressive_tariff_and_maximum_price_are_calculated(): void
    {
        $tariff = Tariff::query()->create([
            'name' => 'Ступени',
            'mode' => Tariff::MODE_PROGRESSIVE,
            'base_km' => 2,
            'base_price' => 200,
            'additional_price_per_km' => 100,
            'max_price' => 700,
            'rounding' => 'none',
        ]);

        $service = app(TariffService::class);

        $this->assertSame(200.0, $service->calculate($tariff, 1.7));
        $this->assertSame(540.0, $service->calculate($tariff, 5.4));
        $this->assertSame(700.0, $service->calculate($tariff, 9.5));
    }

    public function test_zone_tariff_returns_configured_price(): void
    {
        $tariff = Tariff::query()->create([
            'name' => 'Зоны',
            'mode' => Tariff::MODE_ZONES,
            'rounding' => 'none',
        ]);
        TariffZone::query()->create(['tariff_id' => $tariff->id, 'from_km' => 0, 'to_km' => 1, 'price' => 100, 'sort_order' => 0]);
        TariffZone::query()->create(['tariff_id' => $tariff->id, 'from_km' => 1.01, 'to_km' => 3, 'price' => 200, 'sort_order' => 1]);
        TariffZone::query()->create(['tariff_id' => $tariff->id, 'from_km' => 3.01, 'to_km' => null, 'price' => 400, 'sort_order' => 2]);

        $this->assertSame(200.0, app(TariffService::class)->calculate($tariff, 2.5));
        $this->assertSame(400.0, app(TariffService::class)->calculate($tariff, 8));
    }

    public function test_zone_tariff_fails_when_distance_has_no_zone(): void
    {
        $tariff = Tariff::query()->create([
            'name' => 'Неполные зоны',
            'mode' => Tariff::MODE_ZONES,
            'rounding' => 'none',
        ]);
        TariffZone::query()->create(['tariff_id' => $tariff->id, 'from_km' => 0, 'to_km' => 1, 'price' => 100, 'sort_order' => 0]);

        $this->expectException(TariffException::class);

        app(TariffService::class)->calculate($tariff, 2);
    }
}
