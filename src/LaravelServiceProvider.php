<?php
namespace Bnb\GoogleCloudPrint;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'gcp');

        $this->app->singleton('google.print', function ($app) {
            return new GoogleCloudPrint($app['config']);
        });
    }


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $publishPath = config_path('gcp.php');
        } else {
            $publishPath = base_path('config/gcp.php');
        }

        $this->publishes([
            __DIR__ . '/config/config.php' => $publishPath
        ], 'config');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['google.print'];
    }
}