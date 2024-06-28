<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\Tests\TestRouteInterface;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RouteResponseService
{
    private const API_SEPARATOR = '\UI\API';
    private const CONTROLLER_CLASS_POSTFIX = 'Controller';
    private const INJECTION_DATA_INTERFACE = TestRouteInterface::class;

    public function getResponse(DefaultRouteValue|ApiatoRouteValue $routeInfo)
    {
        $controller = $routeInfo->getController();
        $request = $routeInfo->getRequest();

        $responseData = null;
        $injectionData = null;

        $controllerClass = $controller::class;

        if (self::isApiatoApiRoute($controllerClass)) {
            $routeTestClass = self::buildRouteTestClass($controllerClass);

            if (self::hasInjectionData($routeTestClass)) {
                $injectionData = $routeTestClass::getInjectionData();
            }
        }

        if ($injectionData !== null) {
            $dependencies = self::injectRequestData($injectionData, $routeInfo->getDependencies());
            $responseData = self::callController($controllerClass, $routeInfo->getControllerMethod(), $dependencies);
        }

        return $responseData;
    }

    private static function hasInjectionData(string $routeTestClass): bool
    {
        return
            class_exists($routeTestClass) &&
            isset(class_implements($routeTestClass)[self::INJECTION_DATA_INTERFACE]);
    }

    private static function isApiatoApiRoute(string $controllerClass): bool
    {
        return Str::contains($controllerClass, self::API_SEPARATOR);
    }

    private static function buildRouteTestClass(string $controllerClass): string
    {
        $lastSlashIndex = mb_strrpos($controllerClass, '\\');

        [$containerPath, $_] = explode(self::API_SEPARATOR, $controllerClass);

        $controllerClassName = Str::substr(
            string: $controllerClass,
            start: $lastSlashIndex + 1,
            length: Str::length($controllerClass)
        );

        $routeName = Str::remove(self::CONTROLLER_CLASS_POSTFIX, $controllerClassName);

        return "{$containerPath}\\Tests\\Unit\\UI\\API\\Routes\\{$routeName}Test";
    }
    
    public function callController(string $controllerClass, string $method, Collection $dependencies): array
    {
        $controllerInstance = app($controllerClass);

        if ($method === '__invoke') {
            $response = $controllerInstance(...$dependencies);
        } else {
            $response = $controllerInstance->{$method}(...$dependencies);
        }

        $responseData = response()
            ->json($response)
            ->getData(assoc: true);

        if (Arr::has($responseData, 'data')) {
            $responseData = $responseData['data'];
        }

        return $responseData;
    }

    private static function injectRequestData(array $injectionData, Collection $dependencies): Collection
    {
        foreach ($dependencies as $dependency) {
            if ($dependency instanceof Request) {
                $dependency->replace($injectionData);
            }
        }

        return $dependencies;
    }

}
