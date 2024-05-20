<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class RouteInfoValue extends Value
{
    protected ?string $tag;
    protected Collection $rules;

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;
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
