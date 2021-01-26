<?php

namespace Flightsadmin\CrudGenerator;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(Router $router)
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/crudgenerator.php', 'crudgenerator');

        // Register the service the package provides.
        $this->app->singleton('crudgenerator', function ($app) {
            return new CrudGenerator;
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides()
    {
        return ['crudgenerator'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/crudgenerator.php' => config_path('crudgenerator.php'),
        ], 'config');

        // Published stubs
        $this->publishes([
            __DIR__.'/stubs' => base_path('resources/stubs')
        ], 'stubs');

        // Registering package commands.
        $this->commands([
            Commands\GeneratorCommand::class,
            Commands\InstallCommand::class
        ]);
    }
}
