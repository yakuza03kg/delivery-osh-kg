<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'tariff_id',
        'from_km',
        'to_km',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'from_km' => 'float',
        'to_km' => 'float',
        'price' => 'float',
    ];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }
}
