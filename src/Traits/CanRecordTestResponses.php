<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Traits;

use Apiato\Core\Abstracts\Tests\PhpUnit\TestCase as AbstractTestCase;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\SwaggerGeneratorMiddlewareStatesEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;

trait CanRecordTestResponses
{
    private SwaggerGeneratorService $swaggerGeneratorService;

    protected function setUpTheTestEnvironment(): void
    {
        parent::setUpTheTestEnvironment();
        $service = app(SwaggerGeneratorService::class);
        $this->setSwaggerGeneratorService($service);
        $this->getSwaggerGeneratorService()::$STATE = SwaggerGeneratorMiddlewareStatesEnum::ENABLED;
    }

    protected function tearDownTheTestEnvironment(): void
    {
        parent::tearDownTheTestEnvironment();
        $this->getSwaggerGeneratorService()::$STATE = SwaggerGeneratorMiddlewareStatesEnum::DISABLED;
    }

    private function getSwaggerGeneratorService(): SwaggerGeneratorService
    {
        return $this->swaggerGeneratorService;
    }

    private function setSwaggerGeneratorService(SwaggerGeneratorService $swaggerGeneratorService): void
    {
        $this->swaggerGeneratorService = $swaggerGeneratorService;
    }
}
