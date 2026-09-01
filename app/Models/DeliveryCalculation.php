<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DeliveryCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'tariff_id',
        'courier_name',
        'branch_name',
        'branch_address',
        'customer_address',
        'resolved_address',
        'customer_latitude',
        'customer_longitude',
        'distance_km',
        'duration_seconds',
        'price',
        'currency',
        'route_provider',
        'tariff_snapshot',
        'route_geometry',
    ];

    protected $casts = [
        'customer_latitude' => 'float',
        'customer_longitude' => 'float',
        'distance_km' => 'float',
        'duration_seconds' => 'integer',
        'price' => 'float',
        'tariff_snapshot' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function formattedPrice(): string
    {
        return number_format($this->price, 0, ',', ' ').' сом';
    }

    public function formattedDistance(): string
    {
        return number_format($this->distance_km, 1, ',', ' ').' км';
    }

    public function durationLabel(): ?string
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        $minutes = max(1, (int) ceil($this->duration_seconds / 60));

        return $minutes.' мин';
    }

    public function localizedCreatedAt(): ?Carbon
    {
        return $this->created_at?->copy()->setTimezone(config('delivery.timezone'));
    }

    public function formattedCreatedAt(string $format = 'd.m.Y H:i'): string
    {
        return $this->localizedCreatedAt()?->format($format) ?? '';
    }
}
