<?php


namespace LarvaBug\Handler;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class PrepareExceptionData
{

    /**
     * @var array|\Illuminate\Foundation\Application|mixed
     */
    private $blacklist;

    public function __construct()
    {
        $this->blacklist = config('larvabug.blackList') ?? [];
    }

    /**
     * return prepared exception data
     *
     * @author Syed Faisal <sfkazmi0@gmail.com>
     * @param \Throwable $exception
     */
    public function prepare(\Throwable $exception)
    {
        return $this->getExceptionData($exception);
    }

    /**
     * Prepare All exception data
     *
     * @param \Throwable $exception
     * @return array
     */
    private function getExceptionData(\Throwable $exception)
    {
        $data = [];

        $data['exception'] = $exception->getMessage();
        $data['class'] = get_class($exception);
        $data['file'] = $exception->getFile();
        $data['php_version'] = PHP_VERSION;
        $data['server_ip'] = $_SERVER['SERVER_ADDR'] ?? null;
        $data['environment'] = App::environment();
        $data['server_name'] = @gethostname();
        $data['browser'] = $this->getUserBrowser();
        $data['userOs'] = $this->getUserOS();
        $data['host'] = Request::server('SERVER_NAME');
        $data['method'] = Request::method();
        $data['fullUrl'] = Request::fullUrl();
        $data['url'] = Request::path();
        $data['userIp'] = Request::ip();
        $data['line'] = $exception->getLine();
        $data['date_time'] = date("Y-m-d H:i:s");
        $data['session_id'] = Session::getId();
        $data['storage'] = [
            'SERVER' => [
                'USER' => Request::server('USER'),
                'HTTP_USER_AGENT' => Request::server('HTTP_USER_AGENT'),
                'SERVER_PROTOCOL' => Request::server('SERVER_PROTOCOL'),
                'SERVER_SOFTWARE' => Request::server('SERVER_SOFTWARE'),
                'PHP_VERSION' => PHP_VERSION
            ],
            'GET' => $this->filterBlackList(Request::query()),
            'POST' => $this->filterBlackList($_POST),
            'FILE' => Request::file(),
            'OLD' => $this->filterBlackList(Request::hasSession() ? Request::old() : []),
            'COOKIE' => $this->filterBlackList(Request::cookie()),
            'SESSION' => $this->filterBlackList(Request::hasSession() ? Session::all() : []),
            'HEADERS' => $this->filterBlackList(Request::header()),
        ];
        $data['auth_user'] = $this->getAuthUser();
        $data['error'] = $exception->getTraceAsString();
        $data['trace_with_details'] = $this->prepareTraceData($exception->getTrace());

        $data['storage'] = array_filter($data['storage']);

        $count = config('larvabug.lines_count');

        if (!$count || $count > 12) {
            $count = 10;
        }

        $lines = file($data['file']);
        $data['executor'] = [];

        for ($i = -1 * abs($count); $i <= abs($count); $i++) {
            $data['executor'][] = $this->getLineInfo($lines, $data['line'], $i);
        }

        $data['executor'] = array_filter($data['executor']);

        // to make symfony exception more readable
        if ($data['class'] == 'Symfony\Component\Debug\Exception\FatalErrorException') {
            preg_match("~^(.+)' in ~", $data['exception'], $matches);
            if (isset($matches[1])) {
                $data['exception'] = $matches[1];
            }
        }

        return $data;
    }

    /**
     * Filter black listed keys
     *
     * @param $variables
     * @return array
     */
    private function filterBlackList($variables)
    {
        if (is_array($variables)) {
            array_walk($variables, function ($val, $key) use (&$variables) {
                if (is_array($val)) {
                    $variables[$key] = $this->filterBlackList($val);
                }
                if (in_array(strtolower($key), $this->blacklist)) {
                    unset($variables[$key]);
                }
            });
            return $variables;
        }
        return [];
    }

    /**
     * Prepare Trace data with line details
     *
     * @param $trace
     * @return array
     */
    private function prepareTraceData($trace): array
    {
        $count = 5;
        $response = [];

        foreach ($trace as $file)
        {
            if (isset($file['file']) && isset($file['line'])){
                $lines = file($file['file']);
                $data = [];

                for ($i = -1 * abs($count); $i <= abs($count); $i++) {
                    $data[] = $this->getLineInfo($lines, $file['line'], $i);
                }
                $data = array_filter($data);

                $response[] = [
                    'trace' => $file,
                    'content' => $data
                ];
            }
        }

        return $response;
    }

    private function getAuthUser()
    {
        if (function_exists('auth') && auth()->check()) {
            return auth()->user()->toArray();
        }

        if (class_exists(\Cartalyst\Sentinel\Sentinel::class) && $user = Sentinel::check()) {
            return $user->toArray();
        }

        return null;
    }


    /**
     * Gets information from the line
     *
     * @param $lines
     * @param $line
     * @param $i
     *
     * @return array|void
     */
    private function getLineInfo($lines, $line, $i)
    {
        $currentLine = $line + $i;

        $index = $currentLine - 1;

        if (!array_key_exists($index, $lines)) {
            return;
        }
        return [
            'line_number' => $currentLine,
            'line' => $lines[$index]
        ];
    }

    /**
     * Get Request User Browser
     *
     * @return string
     */
    private function getUserBrowser()
    {

        $fullUserBrowser = (!empty($_SERVER['HTTP_USER_AGENT'])?
            $_SERVER['HTTP_USER_AGENT']:getenv('HTTP_USER_AGENT'));
        $userBrowser = explode(')', $fullUserBrowser);
        $userBrowser = $userBrowser[count($userBrowser)-1];

        if((!$userBrowser || $userBrowser === '' || $userBrowser === ' ' || strpos($userBrowser, 'like Gecko') === 1) && strpos($fullUserBrowser, 'Windows') !== false){
            return 'Internet Explorer';
        }else if((strpos($userBrowser, 'Edge/') !== false || strpos($userBrowser, 'Edg/') !== false) && strpos($fullUserBrowser, 'Windows') !== false){
            return 'Microsoft Edge';
        }else if(strpos($userBrowser, 'Chrome/') === 1 || strpos($userBrowser, 'CriOS/') === 1){
            return 'Chrome';
        }else if(strpos($userBrowser, 'Firefox/') !== false || strpos($userBrowser, 'FxiOS/') !== false){
            return 'Mozilla Firefox';
        }else if(strpos($userBrowser, 'Safari/') !== false && strpos($fullUserBrowser, 'Mac') !== false){
            return 'Safari';
        }else if(strpos($userBrowser, 'OPR/') !== false && strpos($fullUserBrowser, 'Opera Mini') !== false){
            return 'Opera Mini';
        }else if(strpos($userBrowser, 'OPR/') !== false){
            return 'Opera';
        }

        return null;
    }

    /**
     * Get request user Operating System
     *
     * @return string
     */
    private function getUserOS(): string
    {

        $user_agent = \request()->header('User-Agent');

        $os_platform  = "Unknown OS Platform";

        $os_array     = array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }



}