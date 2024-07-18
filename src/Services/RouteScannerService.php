<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Enums\OpenAPI\ParametersLocationsEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Exceptions\RouteScanningException;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoContainerInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\ApiatoRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DefaultRouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\RouteScanResultValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionMethod;

class RouteScannerService
{
    private array $ignoreLike;
    private array $ignoreNotLike;
    public array $docBlocks;

    public function __construct(
        private readonly RequestRulesNormalizerService $rulesNormalizerService,
        private readonly DocBlockParserService $docBlockParserService,
        private readonly ConsoleService $consoleService,
    ) {
        $this->ignoreLike = config('swagger.ignore.routes_like');
        $this->ignoreNotLike = config('swagger.ignore.routes_not_like');
        $this->docBlocks = [];
    }

    /**
     * @param Route $route
     * @return ApiatoRouteValue|DefaultRouteValue|null
     */
    public function scanRoute(Route $route): null|ApiatoRouteValue|DefaultRouteValue
    {
        $request = null;
        $errorMessage = null;
        $rules = new Collection();
        $uri = $route->uri();
        $action = $route->getAction();

        if ($this->isIgnorable($uri) || false === $this->hasValidController($action)) {
            $this->skip($route);
            return null;
        }

        $methods = $this->filterRouteMethods($route);
        $reflection = $this->extractRouteReflection($action);
        $apiatoContainerInfo = $this->extractApiatoContainerInfo($reflection);
        $controller = $this->extractRouteController($reflection);
        $controllerMethod = $reflection->getName();
        $dependencies = $this->extractControllerDependencies($reflection);

        try {
            $request = $this->extractControllerRequest($reflection);
            $rules = $this->extractRequestRules($request);
        } catch (RouteScanningException $exception) {
            $errorMessage = $exception->getMessage();
        }

        if (null === $apiatoContainerInfo) {
            $routeInfo = DefaultRouteValue::run();
        } else {
            $this->parseRouteFilesInContainer($uri, $apiatoContainerInfo);
            $routeInfo = ApiatoRouteValue::run()
                ->setApiatoContainerName($apiatoContainerInfo->getContainerName());
        }

        return $routeInfo
            ->setPathInfo($uri)
            ->setDocBlockValue($this->docBlocks[$uri] ?? null)
            ->setScanErrorMessage($errorMessage)
            ->setController($controller)
            ->setControllerMethod($controllerMethod)
            ->setDependencies($dependencies)
            ->setRequest($request)
            ->setRules($rules)
            ->setMethods($methods);
    }

    private function hasValidController(array $routeAction): bool
    {
        return isset($routeAction['uses']) && is_string($routeAction['uses']);
    }

    /**
     * @param array $routeAction
     * @return ReflectionMethod
     * @throws RouteScanningException
     */
    private function extractRouteReflection(array $routeAction): ReflectionMethod
    {
        $reflection = null;

        if ($this->hasValidController($routeAction)) {
            [$controller, $method] = explode('@', $routeAction['uses']);

            if (class_exists($controller)) {
                try {
                    $reflection = new ReflectionMethod($controller, $method);
                } catch (ReflectionException $e) {
                    throw new RouteScanningException($e->getMessage());
                }
            }
        }

        if (null === $reflection) {
            $controllerName = $routeAction['controller'] ?? 'InvalidController';
            throw new RouteScanningException("Не удалость получить рефлексию для {$controllerName}");
        }

        return $reflection;
    }

    private function extractApiatoContainerInfo(ReflectionMethod $reflection): ?ApiatoContainerInfoValue
    {
        $containerInfo = null;

        $controllerPathParts = explode('\\', $reflection->class);
        if (count($controllerPathParts) === 8) {
            if ($controllerPathParts[0] === 'App' &&
                $controllerPathParts[1] === 'Containers' &&
                $controllerPathParts[4] === 'UI' &&
                $controllerPathParts[5] === 'API' &&
                $controllerPathParts[6] === 'Controllers'
            ) {
                $containerInfo = ApiatoContainerInfoValue::run()
                    ->setSectionName($controllerPathParts[2])
                    ->setContainerName($controllerPathParts[3]);
            }
        }

        return $containerInfo;
    }

    /**
     * @param FormRequest|null $request
     * @return Collection
     * @throws RouteScanningException
     */
    private function extractRequestRules(?FormRequest $request): Collection
    {
        try {
            return $request === null
                ? new Collection()
                : $this->rulesNormalizerService->normalize($request->rules());

        } catch (\TypeError $e) {
            throw new RouteScanningException($e->getMessage());
        }
    }

    private function extractRouteController(ReflectionMethod $reflection)
    {
        return app($reflection->getDeclaringClass()->getName());
    }

    private function extractControllerRequest(ReflectionMethod $reflection): ?FormRequest
    {
        $request = null;

        foreach ($reflection->getParameters() as $parameter) {
            $className =  $parameter->getType()?->getName();
            if (is_subclass_of($className, Request::class)) {
                try {
                    $request = new $className();
                } catch (\Exception|\Error $e) {
                    throw new RouteScanningException($e->getMessage());
                }

                break;
            }
        }

        return $request;
    }

    /**
     * @param ReflectionMethod $reflection
     * @return Collection
     */
    private function extractControllerDependencies(ReflectionMethod $reflection): Collection
    {
        $dependencies = new Collection();

        foreach ($reflection->getParameters() as $parameter) {
            $className =  $parameter->getType()?->getName();
            if (null === $className) {
                continue;
            }

            if (is_subclass_of($className, Request::class)) {
                $dependency = new $className();
            } else {
                $dependency = app($className);
            }

            $dependencies->push($dependency);
        }

        return $dependencies;
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

    private function isIgnorable(string $uri): bool
    {
        $isIgnorable = false;

        foreach ($this->ignoreLike as $item) {
            if (Str::contains($uri, $item)) {
                $isIgnorable = true;
                break;
            }
        }

        if ($isIgnorable === false) {
            foreach ($this->ignoreNotLike as $item) {
                if (!Str::contains($uri, $item)) {
                    $isIgnorable = true;
                    break;
                }
            }
        }

        return $isIgnorable;
    }

    private function skip(Route $route): void
    {
        $message = $this->consoleService->concatenate(
            $this->consoleService->space(),
            $this->consoleService->red('ignoring:'),
            $this->consoleService->space(),
            $route->uri()
        );

        $this->consoleService->writeln($message);
    }

    private function parseRouteFilesInContainer(string $uri, ApiatoContainerInfoValue $apiatoContainerInfo): void
    {
        $section = $apiatoContainerInfo->getSectionName();
        $container = $apiatoContainerInfo->getContainerName();

        $path = base_path("app/Containers/$section/$container/UI/API/Routes");

        if (File::exists($path)) {
            $routeFiles = File::allFiles($path);

            /** @var Symfony\Component\Finder\SplFileInfo $file */
            foreach ($routeFiles as $file) {
                $path = $file->getPathName();

                if (false === File::isReadable($path)) {
                    continue;
                }

                $pathAlias = self::extractRealPathAlias($uri);
                $routeFileContents = File::get($path);
                if (false === Str::contains($routeFileContents, $pathAlias)) {
                    continue;
                }

                $docBlock = $this->docBlockParserService->parse($routeFileContents);
                $this->docBlocks[$uri] = $docBlock;
            }
        }
    }

    /**
     * @param string $uri
     * @return string
     *
     * @description
     * извлекает из маршрута вида api/v1/route только route (всё после api/v{version}/)
     */
    private static function extractRealPathAlias(string $uri): string
    {
        $prefix = 'api/';
        $pathAlias = $uri;
        if (Str::contains($uri, $prefix)) {
            $pathAlias = Str::replaceFirst($prefix, '', $uri);
            $firstSlashIndex = Str::position($pathAlias, '/');
            $pathAlias = Str::substr($pathAlias, $firstSlashIndex + 1, Str::length($pathAlias));
        }

        return $pathAlias;
    }
}
