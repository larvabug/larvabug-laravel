<?php


namespace LarvaBug\Provider;


use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use LarvaBug\Client\HttpClient;
use LarvaBug\Handler\LarvaBugExceptionHandler;
use LarvaBug\Handler\PrepareExceptionData;

class ServiceProvider extends BaseServiceProvider
{
    use BootServices;

    /**
     * boot method of service provider
     */
    public function boot()
    {
        $this->bootServices();
    }

    /**
     * Register method of service provider
     */
    public function register()
    {
        $this->app->singleton('larvabug',function ($app){
            $this->mergeConfigFrom(__DIR__ . '/../../config/larvabug.php', 'larvabug');
            return new LarvaBugExceptionHandler(
                new HttpClient(
                    config('larvabug.project_id'),
                    config('larvabug.project_secret')
                ),
                new PrepareExceptionData()
            );
        });
    }


}