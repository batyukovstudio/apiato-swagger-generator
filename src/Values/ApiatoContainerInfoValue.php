<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;

class ApiatoContainerInfoValue extends Value
{
    protected string $sectionName;
    protected string $containerName;

    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    public function setSectionName(string $sectionName): self
    {
        $this->sectionName = $sectionName;
        return $this;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function setContainerName(string $containerName): self
    {
        $this->containerName = $containerName;
        return $this;
    }
}
