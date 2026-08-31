<?php

return [
    'currency' => env('DELIVERY_CURRENCY', 'KGS'),
    'route_provider' => env('ROUTE_PROVIDER', '2gis'),
    'http_timeout' => (int) env('ROUTE_HTTP_TIMEOUT', 8),
    'nominatim_user_agent' => env('NOMINATIM_USER_AGENT', 'Courier KG delivery calculator'),
    'two_gis' => [
        'api_key' => env('MAPS_API_KEY'),
        'locale' => env('TWO_GIS_LOCALE', 'ru_KG'),
        'routing_locale' => env('TWO_GIS_ROUTING_LOCALE', 'en'),
        'search_radius' => (int) env('TWO_GIS_SEARCH_RADIUS', 50000),
        'catalog_url' => env('TWO_GIS_CATALOG_URL', 'https://catalog.api.2gis.com/3.0/items/geocode'),
        'routing_url' => env('TWO_GIS_ROUTING_URL', 'https://routing.api.2gis.com/routing/7.0.0/global'),
    ],
    'nominatim' => [
        'url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org/search'),
    ],
    'osrm' => [
        'url' => env('OSRM_URL', 'https://router.project-osrm.org'),
    ],
];
