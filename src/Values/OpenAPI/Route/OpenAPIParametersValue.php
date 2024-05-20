<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPIParametersValue extends Value
{
    /**
     * REQUIRED. The name of the parameter. Parameter names are case sensitive.
     * If in is "path", the name field MUST correspond to a template expression occurring within the path field in the
     * Paths Object. See Path Templating for further information.
     * If in is "header" and the name field is "Accept", "Content-Type" or "Authorization", the parameter definition
     * SHALL be ignored.
     * For all other cases, the name corresponds to the parameter name used by the in property.
     */
    protected string $name;

    /**
     * REQUIRED. The location of the parameter. Possible values are "query", "header", "path" or "cookie".
     */
    protected string $in;

    /**
     * A brief description of the parameter. This could contain examples of use. CommonMark syntax MAY be used for
     * rich text representation.
     */
    protected ?string $description = null;

    /**
     * Determines whether this parameter is mandatory. If the parameter location is "path", this property is REQUIRED
     * and its value MUST be true. Otherwise, the property MAY be included and its default value is false.
     */
    protected ?bool $required = null;

    /**
     * Specifies that a parameter is deprecated and SHOULD be transitioned out of usage. Default value is false.
     */
    protected ?bool $deprecated = null;

    /**
     * Sets the ability to pass empty-valued parameters. This is valid only for query parameters and allows sending
     * a parameter with an empty value. Default value is false. If style is used, and if behavior is n/a (cannot be
     * serialized), the value of allowEmptyValue SHALL be ignored. Use of this property is NOT RECOMMENDED, as it is
     * likely to be removed in a later revision.
     */
    protected ?bool $allowEmptyValue = null;
    protected ?OpenAPISchemaValue $schema = null;
//    protected $style = null; // todo if needed
//    protected $explode = null; // todo if needed
//    protected $allowReserved = null; // todo if needed
//    protected $example = null; // todo if needed
//    protected $examples = null; // todo if needed
//    protected $content = null; // todo if needed

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIn(): string
    {
        return $this->in;
    }

    public function setIn(string $in): self
    {
        $this->in = $in;
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

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function getDeprecated(): ?bool
    {
        return $this->deprecated;
    }

    public function setDeprecated(?bool $deprecated): self
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    public function getAllowEmptyValue(): ?bool
    {
        return $this->allowEmptyValue;
    }

    public function setAllowEmptyValue(?bool $allowEmptyValue): self
    {
        $this->allowEmptyValue = $allowEmptyValue;
        return $this;
    }

    public function getSchema(): ?OpenAPISchemaValue
    {
        return $this->schema;
    }

    public function setSchema(?OpenAPISchemaValue $schema): self
    {
        $this->schema = $schema;
        return $this;
    }


}