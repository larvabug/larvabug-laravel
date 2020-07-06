<?php


namespace LarvaBug\Provider;


use Illuminate\Support\Facades\Route;
use LarvaBug\Commands\LarvaBugTestCommand;

trait BootServices
{
    /**
     * List of commands to be registered along with provider
     *
     * @var string[]
     */
    protected $commands = [
        LarvaBugTestCommand::class
    ];

    /**
     * BootServices method contains all service that needs to be registered
     */
    private function bootServices()
    {
        $this->publishConfig();
        $this->registerView();
        $this->registerCommands();
        $this->mapLaraBugApiRoutes();
    }

    /**
     * Register view directory with larvabug namespace
     */
    private function registerView()
    {
        $this->app['view']->addNamespace('larvabug',__DIR__.'/../../resources/views');
    }

    /**
     * Map api routes directory to enable all api routes for larvabug
     */
    private function mapLaraBugApiRoutes()
    {
        Route::namespace('\LarvaBug\Http\Controllers')
            ->prefix('larvabug-api')
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Publish package config files that contains package configurations
     */
    private function publishConfig()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '../../config/larvabug.php' => config_path('larvabug.php'),
            ]);
        }
    }

    /**
     * Register array of commands
     */
    private function registerCommands()
    {
        $this->commands($this->commands);
    }
}