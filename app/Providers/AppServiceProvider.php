<?php

namespace App\Providers;

use App\Services\Routes\DemoRouteProvider;
use App\Services\Delivery\ApiUsageService;
use App\Services\Routes\NominatimOsrmRouteProvider;
use App\Services\Routes\RouteProvider;
use App\Services\Routes\TwoGisRouteProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RouteProvider::class, function (): RouteProvider {
            return match (config('delivery.route_provider')) {
                'osrm' => new NominatimOsrmRouteProvider(
                    config('delivery.nominatim.url'),
                    config('delivery.osrm.url'),
                    config('delivery.nominatim_user_agent'),
                    config('delivery.http_timeout'),
                ),
                'demo' => new DemoRouteProvider(),
                default => new TwoGisRouteProvider(
                    config('delivery.two_gis.api_key'),
                    config('delivery.two_gis.catalog_url'),
                    config('delivery.two_gis.routing_url'),
                    config('delivery.http_timeout'),
                    $this->app->make(ApiUsageService::class),
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
