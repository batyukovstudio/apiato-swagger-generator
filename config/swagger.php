<?php

return [

    'base_url' => env('APP_URL', 'http://127.0.0.1:8000/') . env('API_PREFIX', '/api/'),

    'endpoint' => env('SWAGGER_ENDPOINT', 'swagger'), // API Path

    'openapi' => [
        'version' => '3.0.0',

        'info' => [
            'title' => env('APP_NAME', 'Application API Documentation'), // API Title
            'description' => env('APP_DESCRIPTION', 'Documentation for the Application API'), // API Description
            'version' =>  env('APP_VERSION', '1.0.0'), // API Version
        ],
    ],

    'storage' =>  env('SWAGGER_STORAGE', storage_path('swagger')), // API Storage Path

    'ignore' => [ // List of ignored items (routes and methods)
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
