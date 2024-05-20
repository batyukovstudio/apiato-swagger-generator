<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPIRouteValue extends Value
{
    protected Collection $parameters;
    protected ?string $summary = null;
    protected ?string $description = null;
    protected ?Collection $responses = null;
    protected ?Collection $tags = null;

    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function setParameters(Collection $parameters): self
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

    public function getResponses(): ?Collection
    {
        return $this->responses;
    }

    public function setResponses(?Collection $responses): self
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