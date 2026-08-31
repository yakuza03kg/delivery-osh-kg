<?php

namespace App\Services\Routes;

final class RouteResult
{
    public function __construct(
        public readonly int $distanceMeters,
        public readonly ?int $durationSeconds,
        public readonly string $provider,
        public readonly ?string $geometry = null,
        public readonly array $raw = [],
    ) {
    }
}
