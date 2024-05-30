<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\RequestBody;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\OpenAPIContentValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema\OpenAPISchemaParameterValue;
use Illuminate\Support\Collection;

class OpenAPIRequestBodyValue extends Value
{
    protected bool $required;
    protected OpenAPISchemaParameterValue $schema;

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function getSchema(): OpenAPISchemaParameterValue
    {
        return $this->schema;
    }

    public function setSchema(OpenAPISchemaParameterValue $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

}