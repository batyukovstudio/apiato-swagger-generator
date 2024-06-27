<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Apiato\Core\Abstracts\Controllers\Controller;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\RouteValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class ApiatoRouteValue extends RouteValue
{
    protected Controller $controller;
    protected FormRequest $request;
    protected string $controllerMethod;
    protected string $apiatoContainerName;

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function setController(Controller $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function getRequest(): FormRequest
    {
        return $this->request;
    }

    public function setRequest(FormRequest $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getControllerMethod(): string
    {
        return $this->controllerMethod;
    }

    public function setControllerMethod(string $controllerMethod): self
    {
        $this->controllerMethod = $controllerMethod;
        return $this;
    }

    public function getApiatoContainerName(): string
    {
        return $this->apiatoContainerName;
    }

    public function setApiatoContainerName(string $apiatoContainerName): self
    {
        $this->apiatoContainerName = $apiatoContainerName;
        return $this;
    }

}
