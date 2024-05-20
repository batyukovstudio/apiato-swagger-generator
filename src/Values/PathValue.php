<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIRouteValue;

class PathValue extends Value
{
    protected string $uri;
    protected OpenAPIRouteValue $openAPIRouteValue;

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    public function getOpenAPIRouteValue(): OpenAPIRouteValue
    {
        return $this->openAPIRouteValue;
    }

    public function setOpenAPIRouteValue(OpenAPIRouteValue $openAPIRouteValue): self
    {
        $this->openAPIRouteValue = $openAPIRouteValue;
        return $this;
    }

}
