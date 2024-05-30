<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\NotNullFilterable;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPIRouteValue extends Value implements NotNullFilterable
{
    protected ?array $requestBody = null;
    protected ?Collection $parameters = null;
    protected ?string $summary = null;
    protected ?string $description = null;
    protected ?array $responses = null;
    protected ?Collection $tags = null;

    public function getRequestBody(): ?array
    {
        return $this->requestBody;
    }

    public function setRequestBody(?array $requestBody): self
    {
        $this->requestBody = $requestBody;
        return $this;
    }

    public function getParameters(): ?Collection
    {
        return $this->parameters;
    }

    public function setParameters(?Collection $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getResponses(): ?array
    {
        return $this->responses;
    }

    public function setResponses(?array $responses): self
    {
        $this->responses = $responses;
        return $this;
    }

    public function getTags(): ?Collection
    {
        return $this->tags;
    }

    public function setTags(?Collection $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

}