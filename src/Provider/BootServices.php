<?php


namespace LarvaBug\Provider;


use LarvaBug\Commands\LarvaBugTestCommand;

trait BootServices
{
    protected $commands = [
        LarvaBugTestCommand::class
    ];

    protected function bootServices()
    {
        $this->publishConfig();
        $this->registerView();
        $this->registerCommands();
    }

    protected function registerView()
    {
        $this->app['view']->addNamespace('larvabug',__DIR__.'../../resources/views');
    }

    protected function publishConfig()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '../../config/larvabug.php' => config_path('larvabug.php'),
            ]);
        }
    }

    public function registerCommands()
    {
        $this->commands($this->commands);
    }
}