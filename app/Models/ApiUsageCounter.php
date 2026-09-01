<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageCounter extends Model
{
    public const PROVIDER_TWO_GIS = '2gis';
    public const SERVICE_GEOCODER = 'geocoder';
    public const SERVICE_ROUTING = 'routing';

    protected $fillable = [
        'provider',
        'service',
        'quota_limit',
        'baseline_used',
        'requests_used',
        'period_ends_at',
        'last_synced_at',
    ];

    protected $casts = [
        'quota_limit' => 'integer',
        'baseline_used' => 'integer',
        'requests_used' => 'integer',
        'period_ends_at' => 'date',
        'last_synced_at' => 'datetime',
    ];

    public function totalUsed(): int
    {
        return $this->baseline_used + $this->requests_used;
    }

    public function remaining(): int
    {
        return max(0, $this->quota_limit - $this->totalUsed());
    }

    public function usagePercent(): int
    {
        if ($this->quota_limit === 0) {
            return 0;
        }

        return min(100, (int) round($this->totalUsed() / $this->quota_limit * 100));
    }

    public function label(): string
    {
        return match ($this->service) {
            self::SERVICE_ROUTING => 'Построение маршрутов',
            default => 'Поиск адресов',
        };
    }
}
