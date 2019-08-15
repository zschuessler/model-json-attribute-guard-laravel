<?php

namespace Zschuessler\ModelJsonAttributeGuard;

use Zschuessler\ModelJsonAttributeGuard\Console\MakeModelJsonAttributeGuardCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        // Configs
        $this->publishes([
            __DIR__ . '/config/model-json-attributes-guard.php' => config_path('model-json-attributes-guard.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/config/model-json-attributes-guard.php', 'model-json-attributes-guard'
        );
    }

    /**
     * Bootstrap the application services
     *
     * @return void
     */
    public function boot()
    {
        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModelJsonAttributeGuardCommand::class
            ]);
        }
    }
}
