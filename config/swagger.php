<?php

return [

    'base_url' => env('APP_URL', 'http://127.0.0.1:8000/'),

//    'endpoint' => env('SWAGGER_ENDPOINT', 'swagger'),

    'openapi' => [
        'version' => '3.1.0',

        'info' => [
            'title' => env('APP_NAME', 'Application API Documentation'),
            'description' => env('APP_DESCRIPTION', 'Documentation for the Application API'),
            'version' =>  env('APP_VERSION', '1.0.0'),
        ],
    ],

    'storage_endpoint' => 'swagger/documentation.json',

    'ignore' => [
        'routes_like' => [
            'email/verification-notification',
            'email/verify/{id}/{hash}',
            'permissions',
            'profile',
            'password',
            'roles',
            'oauth',
            'passport',
            '_ignition',
            '_debugbar',
            'docs',
            'web',
            'openapi',
            env('SWAGGER_PATH', '/documentation'),
            env('SWAGGER_PATH', '/documentation') . '/content'
        ],
        'routes_not_like' => [
            'api/v1/'
        ],
    ],
];
