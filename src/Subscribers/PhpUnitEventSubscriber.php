<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Subscribers;

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\Contracts\Console\Kernel as ApiatoConsoleKernel;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber as AllTestsFinishedSubscriber;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

final class PhpUnitEventSubscriber implements AllTestsFinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $this->createApplication();
        app(SwaggerGeneratorService::class)->saveResponsesToDisk();;
    }

    protected function createApplication(): void
    {
        $app = require base_path('bootstrap/app.php');
        $app->loadEnvironmentFrom('.env.testing');
        $app->make(ApiatoConsoleKernel::class)->bootstrap();
    }
}