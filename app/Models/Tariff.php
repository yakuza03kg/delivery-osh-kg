<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    use HasFactory;

    public const MODE_PER_KM = 'per_km';
    public const MODE_PROGRESSIVE = 'progressive';
    public const MODE_ZONES = 'zones';

    protected $fillable = [
        'name',
        'mode',
        'price_per_km',
        'base_km',
        'base_price',
        'additional_price_per_km',
        'max_price',
        'rounding',
        'is_active',
    ];

    protected $casts = [
        'price_per_km' => 'float',
        'base_km' => 'float',
        'base_price' => 'float',
        'additional_price_per_km' => 'float',
        'max_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(TariffZone::class)->orderBy('sort_order');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(DeliveryCalculation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function modeLabel(): string
    {
        return match ($this->mode) {
            self::MODE_PROGRESSIVE => 'Прогрессивный',
            self::MODE_ZONES => 'Тарифные зоны',
            default => 'За километр',
        };
    }
}
