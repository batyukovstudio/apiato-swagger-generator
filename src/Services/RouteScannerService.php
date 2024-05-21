<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use ReflectionException;
use ReflectionMethod;

class RouteScannerService
{
    public function scanRoute(Route $route): RouteInfoValue
    {
        $rules = new Collection();
        $scanningError = null;
        $apiatoContainerName = null;

        $methods = $this->filterRouteMethods($route);
        $action = $route->getAction();

        try {
            $reflection = $this->extractRouteReflection($action);
        } catch (ReflectionException $e) {
            $reflection = null;
            $scanningError = $e->getMessage();
        }

        if ($reflection !== null) {
            $apiatoContainerName = $this->extractApiatoContainerName($reflection);

            if ($apiatoContainerName !== null) {
                try {
                    $rules = $this->extractRouteRules($reflection);
                } catch (\Error|\Exception $e){
                    $scanningError = $e->getMessage();
                }
            }
        }

        return RouteInfoValue::run()
            ->setRules($rules)
            ->setMethods($methods)
            ->setScanningError($scanningError)
            ->setApiatoContainerName($apiatoContainerName);
    }

    private function hasValidController(array $routeAction): bool
    {
        return isset($routeAction['uses']) && is_string($routeAction['uses']);
    }

    /**
     * @param array $routeAction
     * @return ReflectionMethod|null
     * @throws ReflectionException
     */
    private function extractRouteReflection(array $routeAction): ?ReflectionMethod
    {
        $reflection = null;

        if ($this->hasValidController($routeAction)) {
            [$controller, $method] = explode('@', $routeAction['uses']);

            if (class_exists($controller)) {
                $reflection = new ReflectionMethod($controller, $method);
            }
        }

        return $reflection;
    }

    private function extractApiatoContainerName(ReflectionMethod $reflection): ?string
    {
        $containerName = false;

        $controllerPathParts = explode('\\', $reflection->class);
        if (count($controllerPathParts) === 8) {
            if ($controllerPathParts[0] === 'App' &&
                $controllerPathParts[1] === 'Containers' &&
                $controllerPathParts[4] === 'UI' &&
                $controllerPathParts[5] === 'API' &&
                $controllerPathParts[6] === 'Controllers'
            ) {
                $containerName = $controllerPathParts[3];
            }
        }

        return $containerName;
    }

    /**
     * @param ReflectionMethod $reflection
     * @return Collection
     */
    private function extractRouteRules(ReflectionMethod $reflection): Collection
    {
        $rules = new Collection();

        foreach ($reflection->getParameters() as $parameter) {
            $className =  $parameter->getType()?->getName();
            if (is_subclass_of($className, Request::class)) {
                $rules = collect((new $className())->rules());
            }
        }

        return $rules;
    }

    private function filterRouteMethods(Route $route): Collection
    {
        $extracted = new Collection();

        $available = array_merge(
            ParametersLocationsEnum::QUERY_METHODS,
            ParametersLocationsEnum::BODY_METHODS
        );

        foreach ($route->methods as $method) {
            if (in_array($method, $available)) {
                $extracted->push(strtolower($method));
            }
        }

        return $extracted;
    }

}
