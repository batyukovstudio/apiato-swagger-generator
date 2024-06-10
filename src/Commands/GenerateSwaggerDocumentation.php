<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Commands;

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

/**
 * Class GenerateSwaggerDocumentation
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Commands
 */
class GenerateSwaggerDocumentation extends Command
{
    protected $signature = 'swagger:generate';
    protected $description = 'Generate Swagger documentation';

    public function handle(SwaggerGeneratorService $swaggerGeneratorService): void
    {
        $documentation = $swaggerGeneratorService->generate();
        Storage::disk('swagger')->put(config('swagger.documentation_filename'), json_encode($documentation));
    }
}
