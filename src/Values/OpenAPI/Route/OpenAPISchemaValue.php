<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPISchemaValue extends Value
{
    protected string $type;
    protected ?string $format = null;
    protected ?Collection $enum = null;
    protected string|int|null $default = null;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function getEnum(): ?Collection
    {
        return $this->enum;
    }

    public function setEnum(?Collection $enum): self
    {
        $this->enum = $enum;
        return $this;
    }

    public function getDefault(): int|string|null
    {
        return $this->default;
    }

    public function setDefault(int|string|null $default): self
    {
        $this->default = $default;
        return $this;
    }
}
