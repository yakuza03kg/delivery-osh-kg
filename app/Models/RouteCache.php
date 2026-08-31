<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteCache extends Model
{
    protected $fillable = [
        'route_hash',
        'origin_latitude',
        'origin_longitude',
        'destination_latitude',
        'destination_longitude',
        'distance_meters',
        'duration_seconds',
        'geometry',
        'provider',
    ];

    protected $casts = [
        'origin_latitude' => 'float',
        'origin_longitude' => 'float',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
        'distance_meters' => 'integer',
        'duration_seconds' => 'integer',
    ];
}
