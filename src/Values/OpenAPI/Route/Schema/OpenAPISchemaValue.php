<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPISchemaValue extends Value
{
    /**
     * https://opis.io/json-schema/2.x/string.html
     */
    protected string $type;
    protected ?string $format = null;
    protected ?Collection $enum = null;
    protected ?int $minimum;
    protected ?int $maximum;
    protected ?int $minLength;
    protected ?int $maxLength;
    protected ?string $pattern;
    protected ?string $contentEncoding;
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

    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    public function setMinimum(?int $minimum): self
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function setMaximum(?int $maximum): self
    {
        $this->maximum = $maximum;
        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function setMinLength(?int $minLength): self
    {
        $this->minLength = $minLength;
        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(?string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;
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
