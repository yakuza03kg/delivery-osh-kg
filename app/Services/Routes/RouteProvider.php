<?php

namespace App\Services\Routes;

interface RouteProvider
{
    public function name(): string;

    public function geocode(string $address, ?Coordinate $near = null): GeocodedAddress;

    public function route(Coordinate $origin, Coordinate $destination): RouteResult;
}
