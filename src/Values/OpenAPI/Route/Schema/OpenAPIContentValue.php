<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPIContentValue extends Value
{
    protected string $type;
    protected OpenAPISchemaValue $schema;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getSchema(): OpenAPISchemaValue
    {
        return $this->schema;
    }

    public function setSchema(OpenAPISchemaValue $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

}
