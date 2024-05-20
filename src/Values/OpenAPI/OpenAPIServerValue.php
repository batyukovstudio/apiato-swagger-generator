<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;

class OpenAPIServerValue extends Value
{
    protected string $url;
    protected string $description;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

}