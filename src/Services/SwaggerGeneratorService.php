<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\QueryParameters\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;


class SwaggerGeneratorService
{
    private const REQUIRED = 'required';
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
                $paths[$uri][$method] = self::generateOpenAPIRoute($method, $routeInfo);
            }
        }

        return $documentation
            ->setPaths($paths)
            ->setTags($tags)
            ->toArray();
    }

    private static function generateOpenAPIRoute(string $method, RouteInfoValue $routeInfo): OpenAPIRouteValue
    {
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
            $requestBody = self::generateOpenAPIRequestBody();
        }

        return OpenAPIRouteValue::run()
            ->setParameters($parameters)
            ->setRequestBody($requestBody)
            ->setTags(collect($tag === null ? self::DEFAULT_TAG : $tag))
            ->setParameters($parameters);
    }

    private static function generateOpenAPIQueryParameters(Collection $rules): Collection
    {
        $parameters = new Collection();

        foreach ($rules as $ruleName => $ruleConditions) {
            $parameter = OpenAPIParametersValue::run()
                ->setName($ruleName)
                ->setDescription(implode('|', $ruleConditions))
                ->setRequired(isset($rule[self::REQUIRED]))
                ->setDeprecated(false)
                ->setIn(ParametersLocationsEnum::BODY)
                ->setSchema(self::generateOpenAPIRequestSchema($ruleName, $ruleConditions));

            $parameters->push($parameter);
        }

        return $parameters;
    }

    private static function generateOpenAPIRequestBody(): array
    {
        return [
            'description' => 'test description',
            'content' => [
                'application/json' => [
                    'schema' => self::generateOpenAPIRequestSchema()
                ],
            ],
        ];
    }

    private static function generateOpenAPIRequestSchema(string $name = null, array $ruleConditions = null): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
                'age' => [
                    'type' => 'string',
                    'minimum' => 0,
                ],
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                ],
            ],
            'required' => ['name', 'email'],
        ];
//        return OpenAPISchemaValue::run()
//            ->setType('string')
//            ->toArray();
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
