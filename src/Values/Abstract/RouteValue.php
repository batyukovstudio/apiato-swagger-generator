<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\DocBlockValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class RouteValue extends Value
{
    protected Collection $dependencies;
    protected Collection $methods;
    protected Collection $rules;
    protected string $pathInfo;
    protected ?Collection $docBlocks;
    protected ?string $scanErrorMessage = null;

    public function getDocBlocks(): Collection
    {
        return $this->docBlocks;
    }

    public function setDocBlocks(Collection $docBlocks): self
    {
        $this->docBlocks = $docBlocks;
        return $this;
    }

    public function getPathInfo(): string
    {
        return $this->pathInfo;
    }

    public function setPathInfo(string $pathInfo): self
    {
        $this->pathInfo = $pathInfo;
        return $this;
    }

    public function getDependencies(): Collection
    {
        return $this->dependencies;
    }

    public function setDependencies(Collection $dependencies): self
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function getMethods(): Collection
    {
        return $this->methods;
    }

    public function setMethods(Collection $methods): self
    {
        $this->methods = $methods;
        return $this;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function setRules(Collection $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    public function getScanErrorMessage(): ?string
    {
        return $this->scanErrorMessage;
    }

    public function setScanErrorMessage(?string $scanErrorMessage): self
    {
        $this->scanErrorMessage = $scanErrorMessage;
        return $this;
    }

}