<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPISchemaValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPIValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionMethod;


class SwaggerGeneratorService
{
    private const AVAILABLE_METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    public function __construct() {
    }

    public function generate()
    {
        $routes = RouteFacade::getRoutes();

        $documentation = $this->generateOpenAPI();
        $paths = $documentation->getPaths();
        $ignored = config('swagger.ignore.routes_like');
        $ignoredNotLike = config('swagger.ignore.routes_not_like');

        /** @var Route $route */
        foreach ($routes as $route) {
            $rules = self::extractRules($route);
            $uri = $route->uri;
            $isValid = true;
            foreach ($ignored as $item) {
                if (str_contains($uri, $item)) {
                    $isValid = false;
                }
            }
            foreach ($ignoredNotLike as $item) {
                if (!str_contains($uri, $item)) {
                    $isValid = false;
                }
            }
            if ($isValid === false) {
                continue;
            }
            if (!isset($paths[$route->uri])) {
                $paths[$route->uri] = collect();
            }
            $paths[$route->uri][strtolower($route->methods[0])] = self::assembleOpenAPIRoute($rules['tag'], $rules['rules']);
        }

        $documentation->setPaths($paths);
//        $documentation->setTags(collect([
//            (object)[
//                'name' => 'User',
//            ],
//        ]));

        return $documentation->toArray();
    }

    private static function extractRules(Route $route): array
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

        return [
            'rules' => $rules,
            'tag' => $tag,
        ];
    }

    private static function assembleOpenAPIRoute(?string $tag, array $rules): OpenAPIRouteValue
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
            ->setTags(collect($tag === null ? 'default' : $tag))
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

}
