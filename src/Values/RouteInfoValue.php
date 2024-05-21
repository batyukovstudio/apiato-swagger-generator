<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class RouteInfoValue extends Value
{
    protected Collection $rules;
    protected Collection $methods;
    protected ?string $scanningError = null;
    protected ?string $apiatoContainerName = null;

    public function getMethods(): Collection
    {
        return $this->method;
    }

    public function setMethods(Collection $methods): self
    {
        $this->method = $methods;
        return $this;
    }

    public function getScanningError(): ?string
    {
        return $this->scanningError;
    }

    public function setScanningError(?string $scanningError): self
    {
        $this->scanningError = $scanningError;
        return $this;
    }

    public function getApiatoContainerName(): ?string
    {
        return $this->apiatoContainerName;
    }

    public function setApiatoContainerName(?string $apiatoContainerName): self
    {
        $this->apiatoContainerName = $apiatoContainerName;
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

}
