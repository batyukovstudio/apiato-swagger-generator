<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPIInfoValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPIValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Servers\OpenAPIServerValue;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use ReflectionMethod;


class SwaggerGeneratorService
{
    public function __construct() {
    }

    public function generate()
    {
        $routes = Route::getRoutes();

        $data = $this->generateOpenAPI();

        /** @var Illuminate\Routing\Route $route */
        foreach ($routes as $route) {
            $action = $route->getAction();
            dd($action);

            if (isset($action['uses']) && is_string($action['uses'])) {
                [$controller, $method] = explode('@', $action['uses']);
                if (class_exists($controller)) {
                    $reflection = new ReflectionMethod($controller, $method);
//                    dd($reflection);
                    foreach ($reflection->getParameters() as $parameter) {
                        $className =  $parameter->getType()?->getName();
                        if ($className !== null && is_subclass_of($className, Request::class)) {
                            $rules = (new $className())->rules();
                            dd($rules);
//                                $rules = app($className)->rules();
//                                dd($rules);
                        }
                    }
                }
            }
        }

        dd($routes);
    }

    private static function extractController()
    {

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
