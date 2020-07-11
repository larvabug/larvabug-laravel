<?php

return [

    /*
     * Id of project from larvabug.com
     *
     */
    'project_id' => env('LB_PROJECT_ID',''),

     /*
      * Project secret from larvabug.com
      *
      */
    'project_secret' => env('LB_SECRET',''),

    /*
     * Array of environments to enable error reporting
     * If environment configured in the array will match with the laravel app environment
     * then error will be reported to larvabug.com
     *
     */
    'environment' => ['production','local'],

    /*
     * Mention any error that should skip from reporting to laravbug
     * Must be mentioned as a string
     *
     */
    'skip_errors' =>  [
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
    ],

    /*
     * Add request parameters to be black listed
     * any parameter defined here will not be reported to larvabug.com and
     * all request, session and cookies will be filtered
     *
     */
    'blacklist' => [
        'password'
    ]

];