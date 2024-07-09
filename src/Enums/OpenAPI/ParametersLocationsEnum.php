<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Enums\OpenAPI;

class ParametersLocationsEnum
{
    const QUERY = 'query';
    const BODY = 'body';
    const HEADER = 'header';
    const COOKIE = 'cookie';

    const QUERY_METHODS = [
        RequestMethodsEnum::GET,
    ];

    const BODY_METHODS = [
        RequestMethodsEnum::POST,
        RequestMethodsEnum::PUT,
        RequestMethodsEnum::PATCH,
        RequestMethodsEnum::DELETE,
    ];
}
