<?php


namespace LarvaBug\Client;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class HttpClient
{
    /**
     * @var string
     */
    private $projectId;
    /**
     * @var string
     */
    private $projectSecret;

    //Local
    private const POST_EXCEPTION = 'http://larvabug.local/api/v1/exception';
    private const VALIDATE_CREDENTIALS = 'http://larvabug.local/api/v1/validate/credentials';
    private const POST_FEEDBACK = 'http://larvabug.local/api/v1/feedback/submit';

    //Development
//    private const POST_EXCEPTION = 'http://dev.larvabug.com/api/v1/exception';
//    private const VALIDATE_CREDENTIALS = 'http://dev.larvabug.com/api/v1/validate/credentials';
//    private const POST_FEEDBACK = 'http://dev.larvabug.com/api/v1/feedback/submit';


    /**
     * @param string $projectId
     * @param string $projectSecret
     */
    public function __construct(string $projectId = null, string $projectSecret = null)
    {
        $this->projectId = $projectId;
        $this->projectSecret = $projectSecret;
    }

    /**
     * Report error to larvabug website
     *
     * @param $exception
     */
    public function report($exceptionData)
    {
        try {
            $data_string = json_encode($exceptionData);

            $result = $this->postRequest($data_string,self::POST_EXCEPTION);

            if ($result &&
                isset($result['status']) &&
                isset($result['exceptionId']) &&
                $result['status'] == 200
            ){
                app('larvabug')->setLastExceptionId($result['exceptionId']);
            }

            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Validate env project id and secret
     *
     * @param array $credentials
     * @return bool
     * @author Syed Faisal <sfkazmi0@gmail.com>
     */
    public function validateCredentials(array $credentials)
    {
        $result = $this->postRequest(json_encode($credentials),self::VALIDATE_CREDENTIALS);

        if ($result && isset($result['status']) && $result['status'] ==  200){
            return true;
        }

        return false;

    }

    /**
     * Curl Request
     *
     * @param $requestData
     * @param $url
     * @return bool|mixed
     */
    private function postRequest($requestData, $url)
    {
        $header = [
            'Content-Type:application/json',
            'Authorization-APP:' . $this->projectId,
            'Authorization-KEY:' . $this->projectSecret
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);

        if ($result) {
            return json_decode($result, true);
        }

        return false;
    }

    /**
     * Submit last exception feedback
     *
     * @param $data
     * @return bool
     */
    public function submitFeedback($data)
    {
        $result = $this->postRequest(json_encode($data),self::POST_FEEDBACK);

        if ($result && isset($result['status']) && $result['status'] ==  200){
            return true;
        }

        return false;

    }

}