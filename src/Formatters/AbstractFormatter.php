<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Formatters;

use Batyukovstudio\ApiatoSwaggerGenerator\Exceptions\ExtensionNotLoaded;

/**
 * Class AbstractFormatter
 * @package Batyukovstudio\ApiatoSwaggerGenerator\Formatters
 */
abstract class AbstractFormatter {

    /**
     * Documentation array
     * @var array
     */
    protected array $documentation;

    /**
     * Formatter constructor.
     * @param array $documentation
     */
    public function __construct(array $documentation) {
        $this->documentation = $documentation;
    }

    /**
     * Format documentation
     * @return string
     * @throws ExtensionNotLoaded
     */
    abstract public function format(): string;

}
