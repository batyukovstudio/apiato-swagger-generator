<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\RouteValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class DefaultRouteValue extends RouteValue
{
    protected mixed $controller;
    protected ?FormRequest $request;
    protected ?string $controllerMethod;

    public function getController(): mixed
    {
        return $this->controller;
    }

    public function setController(mixed $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getRequest(): ?FormRequest
    {
        return $this->request;
    }

    public function setRequest(?FormRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getControllerMethod(): ?string
    {
        return $this->controllerMethod;
    }

    public function setControllerMethod(?string $controllerMethod): self
    {
        $this->controllerMethod = $controllerMethod;
        return $this;
    }

}
