<?php

return [

    'base_url' => env('APP_URL', 'http://127.0.0.1:8000') . env('API_PREFIX', '/api/'),

    'endpoint' => env('SWAGGER_PATH', '/swagger'), // API Path

//    'enable' => env('SWAGGER_ENABLE', true),

    'openapi' => [
        'version' => '3.0.0',

        'info' => [
            'title' => env('APP_NAME', 'Application API Documentation'), // API Title
            'description' => env('APP_DESCRIPTION', 'Documentation for the Application API'), // API Description
            'version' =>  env('APP_VERSION', '1.0.0'), // API Version
        ],

    ],


    'storage' =>  env('SWAGGER_STORAGE', storage_path('swagger')), // API Storage Path

    'views' =>  base_path('resources/views/vendor/swagger'), // API Views Path

    'translations' =>  base_path('resources/lang/vendor/swagger'), // API Translations Path

    'generated' =>  env('SWAGGER_GENERATE_ALWAYS', true), // Always generate schema when accessing Swagger UI

    'append'                    =>  [ // Append additional data to ALL routes
        'responses'             =>  [
            '401'               =>  [
                'description'   =>  '(Unauthorized) Invalid or missing Access Token'
            ]
        ]
    ],

    'ignored' => [ // List of ignored items (routes and methods)
        'methods' => [
            'head',
            'options'
        ],
        'routes' => [
            'passport.authorizations.authorize',
            'passport.authorizations.approve',
            'passport.authorizations.deny',
            'passport.token',
            'passport.tokens.index',
            'passport.tokens.destroy',
            'passport.token.refresh',
            'passport.clients.index',
            'passport.clients.store',
            'passport.clients.update',
            'passport.clients.destroy',
            'passport.scopes.index',
            'passport.personal.tokens.index',
            'passport.personal.tokens.store',
            'passport.personal.tokens.destroy',


            '/_ignition/health-check',
            '/_ignition/execute-solution',
            '/_ignition/share-report',
            '/_ignition/scripts/{script}',
            '/_ignition/styles/{style}',
            env('SWAGGER_PATH', '/documentation'),
            env('SWAGGER_PATH', '/documentation') . '/content'
        ],

        'models' => []
    ],

    'default_tags_generation_strategy' =>  env('SWAGGER_DEFAULT_TAGS_GENERATION_STRATEGY', 'prefix'),

    'parse'                     =>  [ // Parsing strategy
        'docBlock'              =>  true,
        'security'              =>  true,
    ],

    'authentication_flow'       =>  [ // Authentication flow values
        //'OAuth2'                =>  'authorizationCode',
        'bearerAuth'            =>  'http',
    ],

    'security_middlewares'      =>  [
        'auth:api',
        'auth:sanctum',
    ],

    'schema_builders'            => [
        'P' => \Batyukovstudio\ApiatoSwaggerGenerator\Responses\SchemaBuilders\LaravelPaginateSchemaBuilder::class,
        'SP' => \Batyukovstudio\ApiatoSwaggerGenerator\Responses\SchemaBuilders\LaravelSimplePaginateSchemaBuilder::class,
    ]

];
