<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Parameters\Interfaces;

/**
 * Interface ParametersGenerator
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Parameters\Interfaces
 */
interface ParametersGenerator {

    /**
     * Get list of parameters
     * @return array
     */
    public function getParameters(): array;

    /**
     * Get parameter location
     * @return string
     */
    public function getParameterLocation(): string;

}
