<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\OpenAPI\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\SwaggerGeneratorMiddlewareStatesEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIServerValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\QueryParameters\OpenAPIParametersValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPIContentValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPISchemaParameterValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPISchemaValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ResponseValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;


class SwaggerGeneratorService
{
    /**
     * Состояние глобальной миддлвары генератора, когда включена -
     * автоматически прокидывает в сервис все ответы на http запросы
     * Включать $STATE в ENABLED только в главном тест-кейсе и выключать при завершении
     */
    public static SwaggerGeneratorMiddlewareStatesEnum $STATE = SwaggerGeneratorMiddlewareStatesEnum::DISABLED;

    private const REQUIRED = 'required';

    /**
     * Form-like editor is not available for JSON payloads. Here's the corresponding feature request:
     * https://github.com/swagger-api/swagger-ui/issues/2771
     */
    private const APPLICATION_JSON = 'application/json';
    private const METHODS_ORDER = ['get', 'post', 'put', 'patch', 'delete'];

    public function __construct(
        private readonly RouteScannerService $scannerService,
        private readonly RouteResponseService $responseService,
        private readonly ConsoleOutput $output,
    ) {
        $green = new OutputFormatterStyle('green');
        $yellow = new OutputFormatterStyle('yellow');

        $this->output->getFormatter()->setStyle('green', $green);
        $this->output->getFormatter()->setStyle('yellow', $yellow);
    }

    public function pushResponse(Request $request, JsonResponse $response): void
    {
        $this->responseService->pushResponse($request, $response);
    }

    public function saveResponsesToDisk(): void
    {
        $this->responseService->saveResponsesToDisk();
        $status = "<yellow>apiato-swagger-generator: </yellow>";
        $message = "<green>Ответы, полученные с Route-тестов, успешно сохранены на диск</green>\n";
        $this->output->writeln($status . $message);
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

            $tag = match($routeInfo::class) {
                ApiatoRouteValue::class => $routeInfo->getApiatoContainerName(),
                DefaultRouteValue::class => 'Default',
            };

            if ($tags->contains($tag) === false) {
                $tags->push($tag);
            }

            if (!isset($paths[$uri])) {
                $paths[$uri] = new Collection();
            }

            /** @var Collection $routeMethods */
            $routeMethods = $routeInfo->getMethods();

            foreach (self::METHODS_ORDER as $method) {
                if ($routeMethods->contains($method)) {
                    $paths[$uri][$method] = $this->generateOpenAPIRoute($tag, $method, $routeInfo);
                }
            }
        }

        return $documentation
            ->setPaths($paths)
            ->setTags($tags)
            ->toArray();
    }

    private function generateOpenAPIRoute(
        string $tag, string $method, DefaultRouteValue|ApiatoRouteValue $routeInfo): OpenAPIRouteValue
    {
        $loadedResponses = $this->responseService->getResponses($routeInfo);
        $responses = self::generateOpenAPIResponses($loadedResponses);

        $parameters = null;
        $requestBody = null;

        $rules = $routeInfo->getRules();
        $in = in_array(strtoupper($method), ParametersLocationsEnum::BODY_METHODS)
            ? ParametersLocationsEnum::BODY
            : ParametersLocationsEnum::QUERY;

        if ($in === ParametersLocationsEnum::QUERY) {
            $parameters = self::generateOpenAPIQueryParameters($rules);
        } else {
            $requestBody = self::generateOpenAPIRequestBody($rules);
        }

        return OpenAPIRouteValue::run()
            ->setSummary(self::extractSummary($routeInfo))
            ->setTags(collect($tag))
            ->setParameters($parameters)
            ->setRequestBody($requestBody)
            ->setResponses($responses);
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

        $schema = OpenAPISchemaValue::buildRequestSchema($rules);
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

    private static function generateOpenAPIResponses(Collection $responses, string $description = null): array
    {
        $openApiResponses = [];

        /** @var ResponseValue $response */
        foreach ($responses as $response) {
            $content = OpenAPIContentValue::run()
                ->setType(self::APPLICATION_JSON)
                ->setSchema(OpenAPISchemaValue::buildResponseSchema($response->getContent()));

            $openApiResponses[$response->getStatus()] = [
                'description' => $description,
                'content' => [
                    $content->getType() => [
                        'schema' => $content->getSchema()->toArray(),
                    ],
                ],
            ];
        }

        return $openApiResponses;
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

    private static function extractSummary(ApiatoRouteValue | DefaultRouteValue $routeInfo): ?string
    {
        $summary = null !== $routeInfo->getScanErrorMessage()
            ? 'GENERATION ERROR OCCURED: ' . $routeInfo->getScanErrorMessage()
            : null;

        if (null === $summary) {
            $summary = $routeInfo->getDocBlockValue()?->getApiSummary();
        }

        dump($routeInfo->getDocBlockValue()?->getApiSummary());
        return $summary;
    }

}
