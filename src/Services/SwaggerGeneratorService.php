<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\QueryParameters\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPIContentValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPISchemaParameterValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPISchemaValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;


class SwaggerGeneratorService
{
    private const REQUIRED = 'required';
    private const DEFAULT_TAG = 'default';

    /**
     * Form-like editor is not available for JSON payloads. Here's the corresponding feature request:
     * https://github.com/swagger-api/swagger-ui/issues/2771
     */
    private const APPLICATION_JSON = 'application/json';
    private const METHODS_ORDER = ['get', 'post', 'put', 'patch', 'delete'];

    public function __construct(
        private readonly RouteScannerService $scannerService
    ) {
    }

    public function generate(): array
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $documentation = $this->generateOpenAPI();
        $paths = $documentation->getPaths();
        $tags = new Collection();

        /** @var Route $route */
        foreach ($routes as $route) {
            $uri = $route->uri();
            $routeInfo = $this->scannerService->scanRoute($route);
            if ($routeInfo === null) {
                continue;
            }

//            $tag = $routeInfo->getApiatoContainerName();
//            if ($tag !== null && $tags->contains($tag) === false) {
//                $tags->push($tag);
//            }
//
//            if (!isset($paths[$uri])) {
//                $paths[$uri] = new Collection();
//            }

//            /** @var Collection $routeMethods */
//            $routeMethods = $routeInfo->getMethods();
//
//            foreach (self::METHODS_ORDER as $method) {
//                if ($routeMethods->contains($method)) {
//                    $paths[$uri][$method] = self::generateOpenAPIRoute($method, $routeInfo);
//                }
//            }
        }

        return $documentation
            ->setPaths($paths)
            ->setTags($tags)
            ->toArray();
    }

    private static function generateOpenAPIRoute(string $method, RouteInfoValue $routeInfo): OpenAPIRouteValue
    {
        $controller = $routeInfo->getController();
        if (null !== $controller) {
            $response = self::getRouteResponse($controller, $routeInfo);
        }

        $parameters = null;
        $requestBody = null;

        $tag = $routeInfo->getApiatoContainerName();
        $rules = $routeInfo->getRules();
        $in = in_array(strtoupper($method), ParametersLocationsEnum::BODY_METHODS)
            ? ParametersLocationsEnum::BODY
            : ParametersLocationsEnum::QUERY;

        if ($in === ParametersLocationsEnum::QUERY) {
            $parameters = self::generateOpenAPIQueryParameters($rules);
        } else {
            $requestBody = self::generateOpenAPIRequestBody($rules);
        }

        $summary = null !== $routeInfo->getScanningError()
            ? 'GENERATION ERROR OCCURED: ' . $routeInfo->getScanningError()
            : null;

        return OpenAPIRouteValue::run()
            ->setSummary($summary)
            ->setTags(collect($tag === null ? self::DEFAULT_TAG : $tag))
            ->setParameters($parameters)
            ->setRequestBody($requestBody)
//            ->setResponses($responses)
            ->setResponses(null);
    }

    private static function generateOpenAPIQueryParameters(Collection $rules): ?Collection
    {
        $parameters = new Collection();

        foreach ($rules as $ruleName => $ruleConditions) {
            $parameter = OpenAPIParametersValue::run()
                ->setName($ruleName)
                ->setDescription(implode(', ', $ruleConditions))
                ->setRequired(in_array(self::REQUIRED, $ruleConditions))
                ->setDeprecated(false) // TODO
                ->setIn(ParametersLocationsEnum::QUERY)
                ->setSchema(OpenAPISchemaParameterValue::build($ruleConditions)->toArray());

            $parameters->push($parameter);
        }

        if ($parameters->isEmpty()) {
            $parameters = null;
        }

        return $parameters;
    }

    private static function generateOpenAPIRequestBody(Collection $rules): ?array
    {
        $result = null;

        $schema = OpenAPISchemaValue::build($rules);
        if (false === $schema->getProperties()->isEmpty()) {
            $content = OpenAPIContentValue::run()
                ->setType(self::APPLICATION_JSON)
                ->setSchema($schema);

            $result = [
                'content' => [
                    $content->getType() => [
                        'schema' => $content->getSchema()->toArray(),
                    ],
                ],
            ];
        }

        return $result;
    }

    private static function getRouteResponse(Controller $controller, RouteInfoValue $routeInfo): ?array
    {
        $request = $routeInfo->getRequest();
        $response = null;
        $injectionData = null;

        $class = $controller::class;
        $apiSeparator = '\UI\API';
        $controllerClassPostfix = 'Controller';

        $lastSlashIndex = mb_strrpos($class, '\\');

        if (Str::contains($class, $apiSeparator)) {
            [$containerPath, $_] = explode($apiSeparator, $class);
            $controllerClassName = Str::substr($class, $lastSlashIndex + 1, Str::length($class));
            $routeName = Str::remove($controllerClassPostfix, $controllerClassName);

            $routeTestClass = "{$containerPath}\\Tests\\Unit\\UI\\API\\Routes\\{$routeName}Test";
            if (class_exists($routeTestClass) && method_exists($routeTestClass, 'getInjectionData')) {
                $injectionData = $routeTestClass::getInjectionData();
            }
        }

        $dependencies = $routeInfo->getDependencies();

        foreach ($dependencies as $dependency) {
            if ($dependency instanceof Request) {
//                dump($injectionData);
                $dependency::injectData($injectionData ?? []);
//                $request = new $className();
//                $dependency = $request;
            }
        }
        if ($injectionData !== null) {
            dd($dependencies);
        }
//        dump($routeInfo->getDependencies()->count());
//
//        if (null !== $injectionData && null !== $request) {
//            dd($routeInfo->getDependencies());
//            $res = $request::injectData($injectionData);
//            $method = $routeInfo->getControllerMethod();
//            dd(app($controller::class)->__invoke($request));
//            $res = $controller->$method;
//            dd($res);
//        }

        return $response;
    }
    
    private static function generateOpenAPIResponses(Collection $rules): array
    {
        $content = OpenAPIContentValue::run()
            ->setType(self::APPLICATION_JSON)
            ->setSchema(OpenAPISchemaValue::build($rules));

        return [
            '200' => [
                'description' => 'test',
                'content' => [
                    $content->getType() => [
                        'schema' => $content->getSchema()->toArray(),
                    ],
                ],
            ]
        ];
    }

    private static function generateOpenAPI(): OpenAPIValue
    {
        return OpenAPIValue::run()
//            ->setOpenapi('3.23.8')
            ->setOpenapi(config('swagger.openapi.version'))
            ->setInfo(self::generateOpenAPIInfo())
            ->setServers(self::generateOpenAPIServers())
            ->setPaths(collect())
            ->setTags(collect());
    }

    private static function generateOpenAPIInfo(): OpenAPIInfoValue
    {
        return OpenAPIInfoValue::run()
            ->setTitle(config('swagger.openapi.info.title'))
            ->setDescription(config('swagger.openapi.info.description'))
            ->setVersion(config('swagger.openapi.info.version'));
    }

    private static function generateOpenAPIServers(): Collection
    {
        return collect([
            OpenAPIServerValue::run()
                ->setUrl(config('swagger.base_url'))
                ->setDescription('Server'), // TODO
        ]);
    }

}
