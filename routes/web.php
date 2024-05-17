<?php

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Batyukovstudio\ApiatoSwaggerGenerator\Formatter;
use Batyukovstudio\ApiatoSwaggerGenerator\Generator;
use Batyukovstudio\ApiatoSwaggerGenerator\Http\Controllers\SwaggerController;
use Apiato\Core\Foundation\Facades\Apiato;
use Apiato\Core\Loaders\AutoLoaderTrait;


if (Config::get('swagger.enable', true)) {
    Route::prefix(config('swagger.path', '/documentation'))->group(static function() {
        Route::get('', [SwaggerController::class, 'api']);
        Route::get('content', [SwaggerController::class, 'documentation']);
        Route::get('test', function (Repository $configuration, SwaggerGeneratorService $swaggerGeneratorService) {

            dd($swaggerGeneratorService->generate());
//            $countainerPaths = \Apiato\Core\Foundation\Facades\Apiato::getAllContainerPaths();
//            $app = \Apiato\Core\Foundation\Facades\Apiato::getFacadeApplication();
//            (new TestClass())->test();


            dd(123);
            $containers = Apiato::getAllContainerPaths();
            foreach ($containers as $container) {
                dd($container);
            }
            dd($containers);
            dd(111);
//            dd($app);
            dd($countainerPaths);
            $filter = null;
            $format = 'json';

            $documentation = (new Generator($configuration, $filter))->generate();
            dd($documentation);
            $formattedDocs = (new Formatter($documentation))->setFormat($format)->format();

            $storagePath = $configuration->get('swagger.storage');
            File::isDirectory($storagePath) or File::makeDirectory($storagePath, 0777, true, true);
            $file = implode(DIRECTORY_SEPARATOR, [$storagePath, 'swagger.' . $format]);
            file_put_contents($file, $formattedDocs);
        });
    });
}

class TestClass {
    use AutoLoaderTrait;
    public function test()
    {
        $this->runLoadersBoot();
//        $requests = [];
//        $transformers = [];
//
//        // Загрузка всех контейнеров
//        $containers = $this->getAllContainerPaths();
//
//        foreach ($containers as $containerName => $containerPath) {
//            // Получение всех Request классов
//            $requestClasses = $this->getClassesFromContainer($containerPath, 'UI/API/Requests');
//            $requests = array_merge($requests, $requestClasses);
//
//            // Получение всех Transformer классов
//            $transformerClasses = $this->getClassesFromContainer($containerPath, 'UI/API/Transformers');
//            $transformers = array_merge($transformers, $transformerClasses);
//        }
//
//        $this->info("Requests:");
//        foreach ($requests as $request) {
//            $this->line($request);
//        }
//
//        $this->info("\nTransformers:");
//        foreach ($transformers as $transformer) {
//            $this->line($transformer);
//        }
        dd($this);
    }

    private function getClassesFromContainer($containerPath, $subDir)
    {
        $directory = $containerPath . '/' . $subDir;
        $classes = [];

        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $filePath = $directory . '/' . $file;
                if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
                    $className = $this->getClassFromFile($filePath);
                    if ($className) {
                        $classes[] = $className;
                    }
                }
            }
        }

        return $classes;
    }

    private function getClassFromFile($filePath)
    {
        $content = file_get_contents($filePath);
        $namespace = '';
        if (preg_match('/namespace\s+(.+);/', $content, $matches)) {
            $namespace = $matches[1] . '\\';
        }

        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $namespace . $matches[1];
        }

        return null;
    }
}
