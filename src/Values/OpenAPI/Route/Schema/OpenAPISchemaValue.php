<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPISchemaValue extends Value
{
//    protected ?string $type;
    protected Collection $properties;
    protected Collection $required;

    private const REQUIRED = 'required';

    public static function build(Collection $rules): self
    {
        $properties = new Collection();
        $required = new Collection();

        foreach ($rules as $ruleName => $ruleConditions) {
            $properties[$ruleName] = OpenAPISchemaParameterValue::build($ruleConditions);

            if (in_array(self::REQUIRED, $ruleConditions)) {
                $required->push($ruleName);
            }
        }

        return self::run()
//            ->setType('object')
            ->setProperties($properties)
            ->setRequired($required);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function setProperties(Collection $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function getRequired(): Collection
    {
        return $this->required;
    }

    public function setRequired(Collection $required): self
    {
        $this->required = $required;
        return $this;
    }

}
