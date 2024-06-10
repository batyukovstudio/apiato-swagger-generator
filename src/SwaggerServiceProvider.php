<?php 

namespace Batyukovstudio\ApiatoSwaggerGenerator;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Batyukovstudio\ApiatoSwaggerGenerator\Commands\GenerateSwaggerDocumentation;
use Batyukovstudio\ApiatoSwaggerGenerator\Commands\MakeSwaggerSchemaBuilder;

/**
 * Class SwaggerServiceProvider
 * @package Batyukovstudio\ApiatoSwaggerGenerator
 */
class SwaggerServiceProvider extends ServiceProvider
{

    /**
     * @inheritDoc
     * @return void
     */
    public function boot(): void
    {
        config()->set('filesystems.disks.swagger', [
            'driver' => config('swagger.storage_driver'),
            'root' => config('swagger.storage_path'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSwaggerDocumentation::class,
            ]);
        }

        $source = __DIR__ . '/../config/swagger.php';

        $this->publishes([
            $source => config_path('swagger.php')
        ]);

        $viewsPath = __DIR__ . '/../resources/views';
        $this->loadViewsFrom($viewsPath, 'swagger');
        $translationsPath = __DIR__ . '/../resources/lang';

        $this->publishes([
            $viewsPath => config('swagger.views', base_path('resources/views/vendor/swagger')),
            $translationsPath => config('swagger.translations', base_path('resources/lang/vendor/swagger'))
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->mergeConfigFrom(
            $source, 'swagger'
        );

        if (file_exists($file = __DIR__ . '/helpers.php')) {
            require $file;
        }
    }
}
