<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator;

use Batyukovstudio\ApiatoSwaggerGenerator\Subscribers\PhpUnitEventSubscriber;
use PHPUnit\Runner\Extension\Extension as PhpunitExtensionInterface;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class PhpUnitExtension implements PhpunitExtensionInterface
{
    /**
     * @param Configuration $configuration
     * @param EventFacade $facade
     * @param ParameterCollection $parameters
     * @return void
     *
     *
     * подключение:
     * <extensions>
     *    <bootstrap class="Batyukovstudio\ApiatoSwaggerGenerator\PhpUnitExtension">
     *    </bootstrap>
     * </extensions>
 */
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new PhpUnitEventSubscriber());
    }
}
