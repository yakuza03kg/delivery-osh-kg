<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressCache extends Model
{
    protected $fillable = [
        'query_hash',
        'query',
        'formatted_address',
        'latitude',
        'longitude',
        'provider',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
