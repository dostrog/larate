<?php

namespace Dostrog\Larate\Providers;

use Dostrog\Larate\Commands\InstallLarate;
use Dostrog\Larate\Larate;
use Illuminate\Support\ServiceProvider;

class LarateServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/larate.php', 'larate');

        $this->app->singleton('larate', function($app) {
            return new Larate();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../../config/larate.php' => config_path('larate.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/currtest'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/currtest'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/currtest'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);

            $this->commands([
                InstallLarate::class,
            ]);
        }
    }
}
