<?php


namespace LarvaBug\Client;


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

    private const URL = 'http://larvabug.local/api/v1/exception';

    /**
     * @param string $projectId
     * @param string $projectSecret
     */
    public function __construct(string $projectId, string $projectSecret)
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

            $header = [
                'Content-Type:application/json',
                'Authorization-APP:' . $this->projectId,
                'Authorization-KEY:' . $this->projectSecret
            ];

            $ch = curl_init(self::URL);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);

            curl_close($ch);

            if ($result && $result != 404){
                Session::put('lb.lastExceptionId', $result);
            }

            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

}