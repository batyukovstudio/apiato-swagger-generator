<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Subscribers;

use Batyukovstudio\ApiatoSwaggerGenerator\Services\SwaggerGeneratorService;
use Illuminate\Contracts\Console\Kernel as ApiatoConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber as AllTestsFinishedSubscriber;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

final class PhpUnitEventSubscriber implements AllTestsFinishedSubscriber
{
    /**
     * @param Finished $event
     * @return void
     *
     * Класс-подписчик на событие окончания проведения тестов,
     * здесь создаётся экземпляр приложения laravel, чтобы
     * был доступен весь его основной функционал. После создания
     * мы вызываем сервис, хранящий ответы с тестов в памяти
     * для выгрузки этих ответов на диск.
     */
    public function notify(Finished $event): void
    {
        $this->createApplication();

        app(SwaggerGeneratorService::class)->saveResponsesToDisk();

        Artisan::call('swagger:generate');
    }

    protected function createApplication(): void
    {
        $app = require base_path('bootstrap/app.php');
        $app->make(ApiatoConsoleKernel::class)->bootstrap();
    }
}