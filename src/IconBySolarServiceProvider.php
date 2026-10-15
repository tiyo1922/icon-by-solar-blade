<?php

namespace IconBySolar\Blade;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use IconBySolar\Blade\Components\IconBySolar;

class IconBySolarServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/icon-by-solar.php',
            'icon-by-solar'
        );

        $this->app->singleton(SvgResolver::class, function ($app) {
            return new SvgResolver($app['config']['icon-by-solar'] ?? []);
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // Publish configuration when running in console
        $runningInConsole = false;
        if (method_exists($this->app, 'runningInConsole')) {
            $runningInConsole = $this->app->runningInConsole();
        } elseif (function_exists('app') && method_exists(app(), 'runningInConsole')) {
            $runningInConsole = app()->runningInConsole();
        }

        if ($runningInConsole) {
            $configTarget = function_exists('config_path') 
                ? config_path('icon-by-solar.php') 
                : (method_exists($this->app, 'configPath') ? $this->app->configPath('icon-by-solar.php') : null);

            $manifestTarget = function_exists('resource_path')
                ? resource_path('icon-by-solar/manifest.json')
                : (method_exists($this->app, 'resourcePath') ? $this->app->resourcePath('icon-by-solar/manifest.json') : null);

            if ($configTarget) {
                $this->publishes([
                    __DIR__ . '/../config/icon-by-solar.php' => $configTarget,
                ], 'icon-by-solar-config');
            }

            if ($manifestTarget) {
                $this->publishes([
                    __DIR__ . '/../resources/manifest.json' => $manifestTarget,
                ], 'icon-by-solar-manifest');
            }
        }

        // Register Blade components
        Blade::component('icon-by-solar', IconBySolar::class);
        Blade::component('solar-icon', IconBySolar::class);
    }
}
