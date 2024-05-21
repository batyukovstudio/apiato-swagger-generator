<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Services\RouteScannerService;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPISchemaValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;


class SwaggerGeneratorService
{
    private const DEFAULT_TAG = 'default';

    private array $ignoreLike;
    private array $ignoreNotLike;
    private RouteScannerService $scannerService;

    public function __construct(RouteScannerService $scannerService) {
        $this->scannerService = $scannerService;
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
            $routeInfo = $this->scannerService->scanRoute($route);

            $tag = $routeInfo->getApiatoContainerName();
            if ($tag !== null && $tags->contains($tag) === false) {
                $tags->push($tag);
            }

            $uri = $route->uri;

            if ($this->isIgnorable($uri) === false) {
                continue;
            }

            if (!isset($paths[$route->uri])) {
                $paths[$uri] = new Collection();
            }

            foreach ($routeInfo->getMethods() as $method) {
                $paths[$uri][$method] = self::assembleOpenAPIRoute($routeInfo);
            }
        }

        return $documentation
            ->setPaths($paths)
            ->setTags($tags)
            ->toArray();
    }

    private static function assembleOpenAPIRoute(RouteInfoValue $routeInfo): OpenAPIRouteValue
    {
        $parameters = new Collection();
        $tag = $routeInfo->getApiatoContainerName();
        $rules = $routeInfo->getRules();

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

}
