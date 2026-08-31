<?php

namespace App\Services\Routes;

use App\Exceptions\RouteProviderException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

final class TwoGisRouteProvider implements RouteProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $catalogUrl,
        private readonly string $routingUrl,
        private readonly int $timeout,
    ) {
    }

    public function name(): string
    {
        return '2gis';
    }

    public function geocode(string $address): GeocodedAddress
    {
        $this->ensureConfigured();

        try {
            $response = $this->request()->get($this->catalogUrl, [
                'q' => $address,
                'fields' => 'items.point,items.geometry.centroid,items.full_address_name,items.address_name',
                'page_size' => 1,
                'locale' => config('app.locale', 'ru_RU'),
                'key' => $this->apiKey,
            ]);

            if (! $response->successful()) {
                throw new RouteProviderException('2GIS не принял запрос геокодирования.');
            }

            $payload = $response->json();
            $item = data_get($payload, 'items.0') ?? data_get($payload, 'result.items.0');

            if (! is_array($item)) {
                throw new RouteProviderException('2GIS не нашёл такой адрес. Уточните город, улицу и номер дома.');
            }

            $coordinate = $this->coordinateFromItem($item);

            if ($coordinate === null) {
                throw new RouteProviderException('2GIS вернул адрес без координат.');
            }

            return new GeocodedAddress(
                $coordinate,
                (string) ($item['full_address_name'] ?? $item['address_name'] ?? $address),
                $this->name(),
                $item,
            );
        } catch (RouteProviderException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw new RouteProviderException('Сервис 2GIS временно недоступен. Попробуйте ещё раз.');
        }
    }

    public function route(Coordinate $origin, Coordinate $destination): RouteResult
    {
        $this->ensureConfigured();

        try {
            $response = $this->request()->post($this->routingUrl, [
                'points' => [[
                    [
                        'type' => 'stop',
                        'lon' => $origin->longitude,
                        'lat' => $origin->latitude,
                    ],
                    [
                        'type' => 'stop',
                        'lon' => $destination->longitude,
                        'lat' => $destination->latitude,
                    ],
                ]],
                'transport' => 'driving',
                'output' => 'summary',
                'locale' => config('app.locale', 'ru_RU'),
                'route_mode' => 'fastest',
                'traffic_mode' => 'jam',
            ]);

            if (! $response->successful()) {
                throw new RouteProviderException('2GIS не смог построить автомобильный маршрут.');
            }

            $payload = $response->json();
            $route = $this->routePayload($payload);
            $distance = data_get($route, 'distance');

            if (! is_numeric($distance) || (int) $distance < 0) {
                throw new RouteProviderException('Маршрут между адресами не найден.');
            }

            $duration = data_get($route, 'duration');

            return new RouteResult(
                (int) $distance,
                is_numeric($duration) ? (int) $duration : null,
                $this->name(),
                null,
                is_array($route) ? $route : [],
            );
        } catch (RouteProviderException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw new RouteProviderException('Сервис маршрутов 2GIS временно недоступен. Попробуйте ещё раз.');
        }
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->retry(1, 250);
    }

    private function ensureConfigured(): void
    {
        if (blank($this->apiKey)) {
            throw new RouteProviderException('Для 2GIS не задан API-ключ. Добавьте MAPS_API_KEY в .env.');
        }
    }

    private function coordinateFromItem(array $item): ?Coordinate
    {
        $lat = data_get($item, 'point.lat');
        $lon = data_get($item, 'point.lon');

        if (is_numeric($lat) && is_numeric($lon)) {
            return new Coordinate((float) $lat, (float) $lon);
        }

        $wkt = data_get($item, 'geometry.centroid') ?? data_get($item, 'geometry.selection');

        if (is_string($wkt) && preg_match('/POINT\s*\(\s*(-?[0-9.]+)\s+(-?[0-9.]+)\s*\)/i', $wkt, $matches)) {
            return new Coordinate((float) $matches[2], (float) $matches[1]);
        }

        return null;
    }

    private function routePayload(mixed $payload): array
    {
        if (is_array($payload) && isset($payload['distance'])) {
            return $payload;
        }

        if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
            return $payload[0];
        }

        if (is_array($payload) && isset($payload['result'][0]) && is_array($payload['result'][0])) {
            $route = $payload['result'][0];

            if (isset($route['distance'])) {
                return $route;
            }

            $distance = 0;
            $duration = 0;
            foreach ($route['maneuvers'] ?? [] as $maneuver) {
                $distance += (int) data_get($maneuver, 'outcoming_path.distance', 0);
                $duration += (int) data_get($maneuver, 'outcoming_path.duration', 0);
            }

            return ['distance' => $distance, 'duration' => $duration];
        }

        return [];
    }
}
