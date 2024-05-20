<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Commands;

use Illuminate\Console\Command;
use Batyukovstudio\ApiatoSwaggerGenerator\Formatter;
use Batyukovstudio\ApiatoSwaggerGenerator\Generator;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Config\Repository;
use Batyukovstudio\ApiatoSwaggerGenerator\Exceptions\ExtensionNotLoaded;
use Batyukovstudio\ApiatoSwaggerGenerator\Exceptions\InvalidFormatException;

/**
 * Class GenerateSwaggerDocumentation
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Commands
 */
class GenerateSwaggerDocumentation extends Command {

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate Swagger documentation for application';

    /**
     * Config repository instance
     * @var Repository
     */
    protected Repository $configuration;

    /**
     * GenerateSwaggerDocumentation constructor.
     * @param Repository $configuration
     */
    public function __construct(Repository $configuration) {
        $this->configuration = $configuration;
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @throws InvalidFormatException|ExtensionNotLoaded
     */
    public function handle(): void {
        $filter = $this->option('filter') ?: null;
        $format = $this->option('format');
        dd($this->configuration);
        $documentation = (new Generator($this->configuration, $filter))->generate();
        dd($documentation);
        $formattedDocs = (new Formatter($documentation))->setFormat($format)->format();

        $storagePath = $this->configuration->get('swagger.storage');
        File::isDirectory($storagePath) or File::makeDirectory($storagePath, 0777, true, true);
        $file = implode(DIRECTORY_SEPARATOR, [$storagePath, 'swagger.' . $format]);
        file_put_contents($file, $formattedDocs);
    }
}
