<?php

namespace FbrDI;

use Illuminate\Support\ServiceProvider;
use FbrDI\Client\FbrApiClient;
use FbrDI\Commands\SetupCommand;
use FbrDI\Commands\TestConnectionCommand;
use FbrDI\Commands\VerifyTaxpayerCommand;

class FbrDiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fbr-di.php', 'fbr-di');

        $this->app->singleton('fbr-di.client', function ($app) {
            return new FbrApiClient($app['config']['fbr-di'] ?? []);
        });

        $this->app->singleton('fbr-di', function ($app) {
            return new FbrDiManager($app['fbr-di.client']);
        });

        $this->app->alias('fbr-di', FbrDiManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fbr-di.php' => config_path('fbr-di.php'),
            ], 'fbr-di-config');

            $this->commands([
                SetupCommand::class,
                TestConnectionCommand::class,
                VerifyTaxpayerCommand::class,
            ]);
        }
    }
}
