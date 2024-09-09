<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Apiato\Core\Abstracts\Controllers\Controller;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class PathInfoValue extends Value
{
    protected string $method;
    protected string $name;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
