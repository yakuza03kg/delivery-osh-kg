<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\Tariff;
use App\Models\User;
use App\Services\Routes\Coordinate;
use App\Services\Routes\GeocodedAddress;
use App\Services\Routes\RouteProvider;
use App\Services\Routes\RouteResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_can_calculate_delivery_and_result_is_saved(): void
    {
        $courier = User::query()->create([
            'name' => 'Азамат',
            'email' => 'azamat@example.com',
            'password' => 'password',
            'role' => 'courier',
        ]);
        $branch = Branch::query()->create([
            'name' => 'Центр',
            'address' => 'Бишкек, Ленина 100',
            'latitude' => 42.8746,
            'longitude' => 74.5698,
            'is_active' => true,
        ]);
        Tariff::query()->create([
            'name' => 'Стандарт',
            'mode' => Tariff::MODE_PER_KM,
            'price_per_km' => 100,
            'rounding' => 'none',
            'is_active' => true,
        ]);

        $this->app->instance(RouteProvider::class, new TestRouteProvider());

        $response = $this->actingAs($courier)->post(route('delivery.store'), [
            'branch_id' => $branch->id,
            'customer_address' => 'Бишкек, Курманжан Датка 25',
        ]);

        $response->assertRedirect(route('delivery.create'));
        $this->assertDatabaseHas('delivery_calculations', [
            'user_id' => $courier->id,
            'branch_id' => $branch->id,
            'distance_km' => 5.4,
            'price' => 540,
            'route_provider' => 'test',
        ]);

        $calculation = DeliveryCalculation::query()->firstOrFail();
        $this->assertSame('Стандарт', $calculation->tariff_snapshot['name']);
        $this->assertSame('Бишкек, ул. Курманжан Датка, 25', $calculation->resolved_address);
    }

    public function test_guest_can_calculate_delivery_without_an_account(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Ош',
            'address' => 'Ош, Алишер Навои 3',
            'latitude' => 40.523,
            'longitude' => 72.782,
            'is_active' => true,
        ]);
        Tariff::query()->create([
            'name' => 'Стандарт',
            'mode' => Tariff::MODE_PER_KM,
            'price_per_km' => 100,
            'rounding' => 'none',
            'is_active' => true,
        ]);

        $this->app->instance(RouteProvider::class, new TestRouteProvider());

        $response = $this->post(route('delivery.store'), [
            'branch_id' => $branch->id,
            'customer_address' => 'Ош, Шота Руставели 2',
        ]);

        $response->assertRedirect(route('delivery.create'));
        $this->assertDatabaseHas('delivery_calculations', [
            'user_id' => null,
            'courier_name' => 'Гость',
            'branch_id' => $branch->id,
            'price' => 540,
        ]);
    }

    public function test_courier_cannot_open_admin_panel(): void
    {
        $courier = User::query()->create([
            'name' => 'Бек',
            'email' => 'bek@example.com',
            'password' => 'password',
            'role' => 'courier',
        ]);

        $this->actingAs($courier)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}

final class TestRouteProvider implements RouteProvider
{
    public function name(): string
    {
        return 'test';
    }

    public function geocode(string $address, ?Coordinate $near = null): GeocodedAddress
    {
        return new GeocodedAddress(
            new Coordinate(42.9000, 74.6000),
            'Бишкек, ул. Курманжан Датка, 25',
            $this->name(),
        );
    }

    public function route(Coordinate $origin, Coordinate $destination): RouteResult
    {
        return new RouteResult(5400, 780, $this->name());
    }
}
