<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\OpenAPI\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\SwaggerGeneratorMiddlewareStatesEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\DocBlockValue;
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
        private readonly ConsoleService $consoleService,
    ) {
    }

    public function pushResponse(Request $request, $response): void
    {
        if ($response instanceof JsonResponse) {
            $this->responseService->pushResponse($request, $response);
        }
    }

    public function saveResponsesToDisk(): void
    {
        $this->responseService->saveResponsesToDisk();
        $this->pushSuccessfullSavingResponsesToDiskMessage();
    }

    public function generate(): array
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $documentation = $this->generateOpenAPI();
        $paths = $documentation->getPaths();
        $tags = new Collection();

        $this->pushRouteScanStartMessage();

        /** @var Route $route */
        foreach ($routes as $route) {
            $uri = $route->uri();
            $routeInfo = $this->scannerService->scanRoute($route);
            if ($routeInfo === null) {
                continue;
            }

            if (!isset($paths[$uri])) {
                $paths[$uri] = new Collection();
            }

            /** @var Collection $routeMethods */
            $routeMethods = $routeInfo->getMethods();

            foreach (self::METHODS_ORDER as $method) {
                $tag = self::extractTag($method, $routeInfo);
                if ($tags->contains($tag) === false) {
                    $tags->push($tag);
                }

                if ($routeMethods->contains($method)) {
                    $paths[$uri][$method] = $this->generateOpenAPIRoute($tag, $method, $routeInfo);
                }
            }
        }

        $this->pushRouteScanFinishMessage();
        $this->pushSuccessfullGenerationMessage();

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
            ->setSummary(self::extractSummary($method, $routeInfo))
            ->setDescription(self::extractDocBlockDescription($method, $routeInfo))
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

    private static function extractTag(
        string $method, ApiatoRouteValue | DefaultRouteValue $routeInfo): ?string
    {
        /** @var null|DocBlockValue $docBlock */
        $docBlock = $routeInfo->getDocBlocks()[$method] ?? null;

        $tag = $docBlock
            ?->getApiGroup()
            ?->getGroupName();

        if (null === $tag) {
            $tag = match($routeInfo::class) {
                ApiatoRouteValue::class => $routeInfo->getApiatoContainerName(),
                DefaultRouteValue::class => 'Default',
            };
        }

        return $tag;
    }

    private static function extractSummary(
        string $method, ApiatoRouteValue | DefaultRouteValue $routeInfo): ?string
    {
        /** @var null|DocBlockValue $docBlock */
        $docBlock = $routeInfo->getDocBlocks()[$method] ?? null;

        $summary = null !== $routeInfo->getScanErrorMessage()
            ? 'GENERATION ERROR OCCURED: ' . $routeInfo->getScanErrorMessage()
            : null;

        if (null === $summary) {
            $summary = $docBlock
                ?->getApiSummary()
                ?->getText();
        }

        return $summary;
    }

    private static function extractDocBlockDescription(
        string $method, ApiatoRouteValue | DefaultRouteValue $routeInfo): ?string
    {
        /** @var null|DocBlockValue $docBlock */
        $docBlock = $routeInfo->getDocBlocks()[$method] ?? null;

        return $docBlock
            ?->getApiDescription()
            ?->getText();
    }

    private function pushSuccessfullSavingResponsesToDiskMessage(): void
    {
        $message = $this->consoleService->concatenate(
            $this->consoleService->newline(),
            $this->consoleService->space(),
            $this->consoleService->yellow('apiato-swagger-generator:'),
            $this->consoleService->space(),
            $this->consoleService->green('Ответы, полученные с Route-тестов, успешно сохранены на диск'),
            $this->consoleService->newline(),
        );

        $this->consoleService->writeln($message);
    }

    private function pushRouteScanStartMessage(): void
    {
        $message = $this->consoleService->concatenate(
            $this->consoleService->newline(),
            $this->consoleService->space(),
            $this->consoleService->yellow('apiato-swagger-generator:'),
            $this->consoleService->space(),
            $this->consoleService->green('Сканирование маршрутов'),
            $this->consoleService->newline(),
        );

        $this->consoleService->writeln($message);
    }

    private function pushRouteScanFinishMessage(): void
    {
        $message = $this->consoleService->concatenate(
            $this->consoleService->newline(),
            $this->consoleService->space(),
            $this->consoleService->yellow('apiato-swagger-generator:'),
            $this->consoleService->space(),
            $this->consoleService->green('Сканирование маршрутов завершено'),
        );

        $this->consoleService->writeln($message);
    }

    private function pushSuccessfullGenerationMessage(): void
    {
        $message = $this->consoleService->concatenate(
            $this->consoleService->space(),
            $this->consoleService->yellow('apiato-swagger-generator:'),
            $this->consoleService->space(),
            $this->consoleService->green('Генерация завершена успешно, файл:'),
            $this->consoleService->space(),
            $this->consoleService->blue(base_path(config('swagger.storage_endpoint'))),
            $this->consoleService->newline(),
        );

        $this->consoleService->writeln($message);
    }

}
