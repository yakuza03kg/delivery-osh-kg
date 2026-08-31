<?php

namespace App\Services\Delivery;

use App\Exceptions\TariffException;
use App\Models\Tariff;

final class TariffService
{
    public function calculate(Tariff $tariff, float $distanceKm): float
    {
        if ($distanceKm < 0) {
            throw new TariffException('Расстояние не может быть отрицательным.');
        }

        $billingDistance = $this->applyRounding($distanceKm, (string) $tariff->rounding);

        $price = match ($tariff->mode) {
            Tariff::MODE_PROGRESSIVE => $this->progressivePrice($tariff, $billingDistance),
            Tariff::MODE_ZONES => $this->zonePrice($tariff, $billingDistance),
            default => $this->perKilometerPrice($tariff, $billingDistance),
        };

        if ($tariff->max_price !== null) {
            $price = min($price, (float) $tariff->max_price);
        }

        return round(max(0, $price), 2);
    }

    private function perKilometerPrice(Tariff $tariff, float $distanceKm): float
    {
        if ($tariff->price_per_km === null) {
            throw new TariffException('В тарифе не указана стоимость километра.');
        }

        return $distanceKm * (float) $tariff->price_per_km;
    }

    private function progressivePrice(Tariff $tariff, float $distanceKm): float
    {
        if ($tariff->additional_price_per_km === null) {
            throw new TariffException('В прогрессивном тарифе не указана стоимость следующего километра.');
        }

        if ($distanceKm <= (float) $tariff->base_km) {
            return (float) $tariff->base_price;
        }

        return (float) $tariff->base_price
            + ($distanceKm - (float) $tariff->base_km) * (float) $tariff->additional_price_per_km;
    }

    private function zonePrice(Tariff $tariff, float $distanceKm): float
    {
        $zones = $tariff->relationLoaded('zones') ? $tariff->zones : $tariff->zones()->get();

        foreach ($zones as $zone) {
            $matchesFrom = $distanceKm >= (float) $zone->from_km;
            $matchesTo = $zone->to_km === null || $distanceKm <= (float) $zone->to_km;

            if ($matchesFrom && $matchesTo) {
                return (float) $zone->price;
            }
        }

        throw new TariffException('Для этого расстояния не настроена тарифная зона.');
    }

    private function applyRounding(float $distanceKm, string $rounding): float
    {
        return match ($rounding) {
            'ceil' => ceil($distanceKm),
            'nearest' => round($distanceKm),
            'floor' => floor($distanceKm),
            default => $distanceKm,
        };
    }
}
