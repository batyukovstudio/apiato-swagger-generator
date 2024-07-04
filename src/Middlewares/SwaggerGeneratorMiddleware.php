<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Middlewares;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\SwaggerGeneratorMiddlewareStatesEnum;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Apiato\Core\Abstracts\Middlewares\Middleware;
use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Closure;

class SwaggerGeneratorMiddleware extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $state = SwaggerGeneratorService::$STATE;

        $response = $next($request);
        if ($state === SwaggerGeneratorMiddlewareStatesEnum::ENABLED) {
            /** @var JsonResponse $response */
            app(SwaggerGeneratorService::class)->pushResponse($request, $response);
        }

        return $response;
    }
}