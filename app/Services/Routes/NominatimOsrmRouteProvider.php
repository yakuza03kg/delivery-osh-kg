<?php

namespace App\Services\Routes;

use App\Exceptions\RouteProviderException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

final class NominatimOsrmRouteProvider implements RouteProvider
{
    public function __construct(
        private readonly string $geocoderUrl,
        private readonly string $routerUrl,
        private readonly string $userAgent,
        private readonly int $timeout,
    ) {
    }

    public function name(): string
    {
        return 'nominatim_osrm';
    }

    public function geocode(string $address): GeocodedAddress
    {
        try {
            $response = $this->request()->withHeaders([
                'User-Agent' => $this->userAgent,
                'Referer' => config('app.url'),
            ])->get($this->geocoderUrl, [
                'q' => $address,
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if (! $response->successful()) {
                throw new RouteProviderException('Геокодер не принял запрос.');
            }

            $result = $response->json('0');

            if (! is_array($result) || ! is_numeric($result['lat'] ?? null) || ! is_numeric($result['lon'] ?? null)) {
                throw new RouteProviderException('Адрес не найден. Уточните город, улицу и номер дома.');
            }

            return new GeocodedAddress(
                new Coordinate((float) $result['lat'], (float) $result['lon']),
                (string) ($result['display_name'] ?? $address),
                $this->name(),
                $result,
            );
        } catch (RouteProviderException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw new RouteProviderException('Сервис геокодирования временно недоступен. Попробуйте ещё раз.');
        }
    }

    public function route(Coordinate $origin, Coordinate $destination): RouteResult
    {
        $url = rtrim($this->routerUrl, '/').'/route/v1/driving/'
            .$origin->longitude.','.$origin->latitude.';'
            .$destination->longitude.','.$destination->latitude;

        try {
            $response = $this->request()->get($url, [
                'overview' => 'false',
                'alternatives' => 'false',
                'steps' => 'false',
            ]);

            if (! $response->successful() || $response->json('code') !== 'Ok') {
                throw new RouteProviderException('Маршрут между адресами не найден.');
            }

            $route = $response->json('routes.0');

            if (! is_array($route) || ! is_numeric($route['distance'] ?? null)) {
                throw new RouteProviderException('Маршрут между адресами не найден.');
            }

            return new RouteResult(
                (int) round((float) $route['distance']),
                is_numeric($route['duration'] ?? null) ? (int) round((float) $route['duration']) : null,
                $this->name(),
                null,
                $route,
            );
        } catch (RouteProviderException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw new RouteProviderException('Сервис маршрутов временно недоступен. Попробуйте ещё раз.');
        }
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout($this->timeout)
            ->retry(1, 250);
    }
}
