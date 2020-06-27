<?php


namespace LarvaBug\Handler;

use Illuminate\Support\Facades\App;
use LarvaBug\Client\HttpClient;

class LarvaBugExceptionHandler
{
    /**
     * @var HttpClient
     */
    private $client;
    /**
     * @var PrepareExceptionData
     */
    private $exceptionData;

    /**
     * LarvaBugExceptionHandler constructor.
     * @param HttpClient $client
     * @param PrepareExceptionData $exceptionData
     */
    public function __construct(
        HttpClient $client,
        PrepareExceptionData $exceptionData
    )
    {
        $this->client = $client;
        $this->exceptionData = $exceptionData;
    }

    public function handle(\Throwable $exception)
    {
        if (!$this->checkAppEnvironment()){
            return false;
        }

        if ($this->skipError(get_class($exception))){
            return false;
        }

        $data = $this->exceptionData->prepare($exception);

        $this->client->report($data);

        return true;
    }

    /**
     * Check if larvabug environment configurations match with app environment
     *
     * @return bool
     */
    public function checkAppEnvironment()
    {
        if (!config('larvabug.environment')){
            return false;
        }

        if (is_array(config('larvabug.environment'))){
            if (count(config('larvabug.environment')) == 0){
                return false;
            }

            if (in_array(App::environment(),config('larvabug.environment'))){
                return true;
            }
        }

        return false;
    }

    protected function skipError($class)
    {
        if (in_array($class,config('larvabug.skip_errors'))){
            return true;
        }

        return false;
    }
}