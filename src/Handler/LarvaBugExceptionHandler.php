<?php


namespace LarvaBug\Handler;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
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
     * @var string
     */
    private $lastExceptionId;

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

    public function report(\Throwable $exception)
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
    private function checkAppEnvironment()
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

    /**
     * Error dont report, configured in config file
     *
     * @param $class
     * @return bool
     */
    private function skipError($class)
    {
        if (in_array($class,config('larvabug.skip_errors'))){
            return true;
        }

        return false;
    }

    /**
     * Validate env credentials from larvabug
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(array $credentials)
    {
        return $this->client->validateCredentials($credentials);
    }

    /**
     * Collect error feedback from user
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function collectFeedback()
    {
        if ($this->lastExceptionId) {
            return redirect($this->feedbackUrl().'?exceptionId='.$this->lastExceptionId);
        }

        return redirect('/');
    }

    /**
     * Get feedback url
     *
     * @return mixed
     * @author Syed Faisal <sfkazmi0@gmail.com>
     */
    public function feedbackUrl()
    {
        return URL::to('larvabug-api/collect/feedback');
    }

    /**
     * Submit user collected feedback
     *
     * @param $data
     * @return bool
     */
    public function submitFeedback($data)
    {
        $this->client->submitFeedback($data);

        return true;
    }

    public function setLastExceptionId(string $exceptionId)
    {
        return $this->lastExceptionId = $exceptionId;
    }

    public function getLastExceptionId()
    {
        return $this->lastExceptionId;
    }

}