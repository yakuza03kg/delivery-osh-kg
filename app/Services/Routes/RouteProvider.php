<?php

namespace App\Services\Routes;

interface RouteProvider
{
    public function name(): string;

    public function geocode(string $address): GeocodedAddress;

    public function route(Coordinate $origin, Coordinate $destination): RouteResult;
}
