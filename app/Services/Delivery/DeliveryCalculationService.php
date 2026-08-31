<?php

namespace App\Services\Delivery;

use App\Exceptions\RouteProviderException;
use App\Exceptions\TariffException;
use App\Models\AddressCache;
use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\RouteCache;
use App\Models\Tariff;
use App\Models\User;
use App\Services\Routes\Coordinate;
use App\Services\Routes\RouteProvider;
use App\Services\Routes\RouteResult;
use Illuminate\Support\Facades\DB;

final class DeliveryCalculationService
{
    public function __construct(
        private readonly RouteProvider $routeProvider,
        private readonly TariffService $tariffService,
    ) {
    }

    public function calculate(User $user, Branch $branch, string $customerAddress): DeliveryCalculation
    {
        if (! $branch->hasCoordinates()) {
            throw new RouteProviderException('У выбранного заведения не указаны координаты. Обратитесь к администратору.');
        }

        $tariff = Tariff::query()->with('zones')->active()->first();

        if (! $tariff) {
            throw new TariffException('Активный тариф ещё не настроен. Обратитесь к администратору.');
        }

        $provider = $this->routeProvider;
        $origin = new Coordinate((float) $branch->latitude, (float) $branch->longitude);
        $normalizedAddress = $this->normalize($customerAddress);
        $addressHash = hash('sha256', $provider->name().'|'.$normalizedAddress);
        $addressCache = AddressCache::query()->where('query_hash', $addressHash)->first();

        if ($addressCache) {
            $geocoded = new \App\Services\Routes\GeocodedAddress(
                new Coordinate($addressCache->latitude, $addressCache->longitude),
                $addressCache->formatted_address,
                $addressCache->provider,
            );
        } else {
            $geocoded = $provider->geocode($customerAddress, $origin);
            AddressCache::query()->updateOrCreate(
                ['query_hash' => $addressHash],
                [
                    'query' => $customerAddress,
                    'formatted_address' => $geocoded->formattedAddress,
                    'latitude' => $geocoded->coordinate->latitude,
                    'longitude' => $geocoded->coordinate->longitude,
                    'provider' => $geocoded->provider,
                ],
            );
        }

        $routeHash = hash('sha256', implode('|', [
            $provider->name(),
            $this->coordinateKey($origin),
            $this->coordinateKey($geocoded->coordinate),
        ]));
        $routeCache = RouteCache::query()->where('route_hash', $routeHash)->first();

        if ($routeCache) {
            $route = new RouteResult(
                $routeCache->distance_meters,
                $routeCache->duration_seconds,
                $routeCache->provider,
                $routeCache->geometry,
            );
        } else {
            $route = $provider->route($origin, $geocoded->coordinate);
            RouteCache::query()->updateOrCreate(
                ['route_hash' => $routeHash],
                [
                    'origin_latitude' => $origin->latitude,
                    'origin_longitude' => $origin->longitude,
                    'destination_latitude' => $geocoded->coordinate->latitude,
                    'destination_longitude' => $geocoded->coordinate->longitude,
                    'distance_meters' => $route->distanceMeters,
                    'duration_seconds' => $route->durationSeconds,
                    'geometry' => $route->geometry,
                    'provider' => $route->provider,
                ],
            );
        }

        $distanceKm = round($route->distanceMeters / 1000, 2);
        $price = $this->tariffService->calculate($tariff, $distanceKm);

        return DB::transaction(fn (): DeliveryCalculation => DeliveryCalculation::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'tariff_id' => $tariff->id,
            'courier_name' => $user->name,
            'branch_name' => $branch->name,
            'branch_address' => $branch->address,
            'customer_address' => $customerAddress,
            'resolved_address' => $geocoded->formattedAddress,
            'customer_latitude' => $geocoded->coordinate->latitude,
            'customer_longitude' => $geocoded->coordinate->longitude,
            'distance_km' => $distanceKm,
            'duration_seconds' => $route->durationSeconds,
            'price' => $price,
            'currency' => config('delivery.currency', 'KGS'),
            'route_provider' => $route->provider,
            'tariff_snapshot' => $this->tariffSnapshot($tariff),
            'route_geometry' => $route->geometry,
        ]));
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    private function coordinateKey(Coordinate $coordinate): string
    {
        return number_format($coordinate->latitude, 6, '.', '').','
            .number_format($coordinate->longitude, 6, '.', '');
    }

    private function tariffSnapshot(Tariff $tariff): array
    {
        return [
            'id' => $tariff->id,
            'name' => $tariff->name,
            'mode' => $tariff->mode,
            'price_per_km' => $tariff->price_per_km,
            'base_km' => $tariff->base_km,
            'base_price' => $tariff->base_price,
            'additional_price_per_km' => $tariff->additional_price_per_km,
            'max_price' => $tariff->max_price,
            'rounding' => $tariff->rounding,
            'zones' => $tariff->zones->map(fn ($zone): array => [
                'from_km' => $zone->from_km,
                'to_km' => $zone->to_km,
                'price' => $zone->price,
            ])->values()->all(),
        ];
    }
}
