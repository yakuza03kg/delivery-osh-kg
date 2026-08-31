<?php

namespace App\Services\Routes;

use App\Exceptions\RouteProviderException;

final class DemoRouteProvider implements RouteProvider
{
    public function name(): string
    {
        return 'demo';
    }

    public function geocode(string $address, ?Coordinate $near = null): GeocodedAddress
    {
        if (! preg_match('/^\s*(-?[0-9]+(?:[.,][0-9]+)?)\s*[,; ]\s*(-?[0-9]+(?:[.,][0-9]+)?)\s*$/u', $address, $matches)) {
            throw new RouteProviderException('Демо-режим принимает координаты клиента в формате: 42.8746, 74.5698. Для адресов подключите 2GIS или OSRM.');
        }

        $latitude = (float) str_replace(',', '.', $matches[1]);
        $longitude = (float) str_replace(',', '.', $matches[2]);

        return new GeocodedAddress(
            new Coordinate($latitude, $longitude),
            $address,
            $this->name(),
        );
    }

    public function route(Coordinate $origin, Coordinate $destination): RouteResult
    {
        $distance = $this->haversine($origin, $destination) * 1.25;

        return new RouteResult(
            (int) round($distance),
            (int) round(($distance / 1000) / 25 * 3600),
            $this->name(),
        );
    }

    private function haversine(Coordinate $origin, Coordinate $destination): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($destination->latitude - $origin->latitude);
        $lonDelta = deg2rad($destination->longitude - $origin->longitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($origin->latitude))
            * cos(deg2rad($destination->latitude))
            * sin($lonDelta / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }
}
