<?php

return [

    'project_id' => env('LB_PROJECT_ID',''),

    'project_secret' => env('LB_SECRET',''),

    'environment' => ['production','local'],

    'skip_errors' =>  [
        '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class'
    ],

    'blacklist' => [
        'password'
    ]

];