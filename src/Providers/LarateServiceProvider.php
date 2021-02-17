<?php
declare(strict_types=1);

namespace Dostrog\Larate\Providers;

use Dostrog\Larate\Commands\InstallLarate;
use Dostrog\Larate\Contracts\ExchangeRateService;
use Dostrog\Larate\Larate;
use Illuminate\Support\ServiceProvider;

class LarateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/larate.php', 'larate');

        $this->app->bind(
            ExchangeRateService::class,
            config('larate.service')[config('larate.default_base_currency')]
        );

        $this->app->singleton('larate', Larate::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../../config/larate.php' => config_path('larate.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/larate'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/larate'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/larate'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);

            $this->commands([
                InstallLarate::class,
            ]);
        }
    }
}
