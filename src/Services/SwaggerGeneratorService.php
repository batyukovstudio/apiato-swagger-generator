<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPISchemaValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as Route;
use Illuminate\Support\Collection;
use ReflectionMethod;


class SwaggerGeneratorService
{
    private const AVAILABLE_METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];
    private const DEFAULT_TAG = 'Default';

    private array $ignoreLike;
    private array $ignoreNotLike;

    public function __construct() {
        $this->ignoreLike = config('swagger.ignore.routes_like');
        $this->ignoreNotLike = config('swagger.ignore.routes_not_like');
    }

    public function generate(): array
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $documentation = $this->generateOpenAPI();
        $paths = $documentation->getPaths();
        $tags = new Collection();

        /** @var Route $route */
        foreach ($routes as $route) {
            $routeInfo = self::extractRouteInfo($route);

            $tag = $routeInfo->getTag();
            if ($tag !== null && $tags->contains($tag) === false) {
                $tags->push($routeInfo->getTag());
            }

            $uri = $route->uri;

            if ($this->isIgnorable($uri) === false) {
                continue;
            }

            if (!isset($paths[$route->uri])) {
                $paths[$route->uri] = new Collection();
            }

            $routeMethods = self::filterRouteMethods($route->methods);
            foreach ($routeMethods as $routeMethod) {
                $paths[$uri][$routeMethod] = self::assembleOpenAPIRoute($tag, $routeInfo->getRules());
            }
        }

        return $documentation
            ->setPaths($paths)
            ->setTags($tags)
            ->toArray();
    }

    private static function extractRouteInfo(Route $route): RouteInfoValue
    {
        $tag = null;
        $rules = [];
        $action = $route->getAction();

        if (isset($action['uses']) && is_string($action['uses'])) {  // существует валидный контроллер
            [$controller, $method] = explode('@', $action['uses']);
            if (class_exists($controller)) {
                $reflection = new ReflectionMethod($controller, $method);

                $controllerPathParts = explode('\\', $controller);
                if (count($controllerPathParts) === 8) {  // Apiato controllers only
                    if ($controllerPathParts[0] === 'App' &&
                        $controllerPathParts[1] === 'Containers' &&
                        $controllerPathParts[4] === 'UI' &&
                        $controllerPathParts[5] === 'API' &&
                        $controllerPathParts[6] === 'Controllers'
                    ) {
                        $tag = $controllerPathParts[3];

                        foreach ($reflection->getParameters() as $parameter) {
                            $className =  $parameter->getType()?->getName();
                            if (is_subclass_of($className, Request::class)) {
                                $rules = (new $className())->rules();
                            }
                        }
                    }
                }
            }
        }

        return RouteInfoValue::run()
            ->setTag($tag)
            ->setRules(collect($rules));
    }

    private static function assembleOpenAPIRoute(?string $tag, Collection $rules): OpenAPIRouteValue
    {
        $parameters = new Collection();

        foreach ($rules as $ruleName => $rule) {
            if (is_array($rule)) {
                $ruleParameters = $rule;
                foreach ($ruleParameters as $key => $ruleParameter) {
                    if (!is_string($ruleParameter)) {
                        $ruleParameter = $ruleParameter::class; // TODO: стратегии обработки разных классов Rule
                    }
                    $ruleParameters[$key] = $ruleParameter;
                }
                $rule = implode('|', $ruleParameters);
            } elseif (!is_string($rule)) {
                $rule = $rule::class;
            }

            $ruleFull = $rule;
            $ruleParameters = array_flip(explode('|', $rule));

            $parameter = OpenAPIParametersValue::run()
                ->setName($ruleName)
                ->setDescription($ruleFull)
                ->setRequired(isset($ruleParameters['required']))
                ->setDeprecated(false)
                ->setIn('query')
                ->setSchema(OpenAPISchemaValue::run()->setType('string'));

            $parameters->push($parameter);
        }

        return OpenAPIRouteValue::run()
            ->setTags(collect($tag === null ? self::DEFAULT_TAG : $tag))
            ->setParameters($parameters);
    }

    private static function generateOpenAPI(): OpenAPIValue
    {
        return OpenAPIValue::run()
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

    private function isIgnorable(string $uri): bool
    {
        $isValid = true;

        foreach ($this->ignoreLike as $item) {
            if (str_contains($uri, $item)) {
                $isValid = false;
            }
        }

        foreach ($this->ignoreNotLike as $item) {
            if (!str_contains($uri, $item)) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    private static function filterRouteMethods(array $methods): Collection
    {
        $extracted = new Collection();

        foreach ($methods as $method) {
            if (in_array($method, self::AVAILABLE_METHODS)) {
                $extracted->push(strtolower($method));
            }
        }

        return $extracted;
    }

}
