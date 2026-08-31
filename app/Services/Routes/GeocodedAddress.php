<?php

namespace App\Services\Routes;

final class GeocodedAddress
{
    public function __construct(
        public readonly Coordinate $coordinate,
        public readonly string $formattedAddress,
        public readonly string $provider,
        public readonly array $raw = [],
    ) {
    }
}
