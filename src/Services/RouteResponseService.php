<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\Tests\TestRouteInterface;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RouteResponseService
{
    private static array $RESPONSES;
    private array $loadedResponses;
    private bool $loadedResponsesFail;
    private const RESPONSES_FILENAME = 'test-responses.json';

    private const API_SEPARATOR = '\UI\API';
    private const CONTROLLER_CLASS_POSTFIX = 'Controller';
    private const INJECTION_DATA_INTERFACE = TestRouteInterface::class;

    public function __construct()
    {
        $this->loadedResponses = [];
        $this->loadedResponsesFail = false;
    }

    public function pushResponse(Request $request, JsonResponse $response): void
    {
        $responseContent = json_decode($response->getContent(), associative: true);
        if (isset($responseContent['data'])) {
            $responseContent = $responseContent['data'];
        }

        self::$RESPONSES[$request->getPathInfo()] = [
            'status' => $response->getStatusCode(),
            'response' => [
                'data' => $responseContent,
            ],
        ];
    }

    public function saveResponsesToDisk(): void
    {
        Storage::disk('swagger')->put(self::RESPONSES_FILENAME, json_encode(self::$RESPONSES));
    }

    public function getResponse(DefaultRouteValue|ApiatoRouteValue $routeInfo): ?array
    {
        if (empty($this->loadedResponses) && $this->loadedResponsesFail !== true) {
            $loaded = Storage::disk('swagger')->get(self::RESPONSES_FILENAME);
            if ($loaded !== null) {
                $this->loadedResponses = json_decode($loaded, associative: true);
            } else {
                $this->loadedResponsesFail = true;
            }
        }

        $pathInfo = $routeInfo->getPathInfo();
//        dump($pathInfo);
//        dump($this->loadedResponses);
        return $this->loadedResponses[$pathInfo] ?? null;
//
//        $controller = $routeInfo->getController();
//        $request = $routeInfo->getRequest();
//
//        $responseData = null;
//        $injectionData = null;
//
//        $controllerClass = $controller::class;
//
//        if (self::isApiatoApiRoute($controllerClass)) {
//            $routeTestClass = self::buildRouteTestClass($controllerClass);
//
//            if (self::hasInjectionData($routeTestClass)) {
//                $injectionData = $routeTestClass::getInjectionData();
//            }
//        }
//
//        if ($injectionData !== null) {
//            $dependencies = self::injectRequestData($injectionData, $routeInfo->getDependencies());
//            $responseData = self::callController($controllerClass, $routeInfo->getControllerMethod(), $dependencies);
//        }
//
//        return $responseData;
    }

    private static function hasInjectionData(string $routeTestClass): bool
    {
        try {
            class_exists($routeTestClass);
        } catch (\Exception | \Error) {
            return false;
        }

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
