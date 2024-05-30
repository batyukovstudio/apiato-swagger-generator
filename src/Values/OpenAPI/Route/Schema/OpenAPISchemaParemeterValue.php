<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\OpenAPI\Route\Schema;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\NotNullFilterable;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\SchemaParameterTypesEnum;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OpenAPISchemaParemeterValue extends Value implements NotNullFilterable
{
    /**
     * https://opis.io/json-schema/2.x/string.html
     */
    protected string $type;
    protected ?string $description = null;
    protected null|array|string|int|float $example = null;
    protected ?string $format = null;
    protected ?Collection $enum = null;
    protected ?int $minimum = null;
    protected ?int $maximum = null;
    protected ?int $minLength = null;
    protected ?int $maxLength = null;
    protected ?string $pattern = null;
    protected ?string $contentEncoding = null;
    protected string|int|null $default = null;

    private const TYPE_MAPPING = [
        'numeric' => SchemaParameterTypesEnum::NUMBER,
        'int' => SchemaParameterTypesEnum::INTEGER,
        'integer' => SchemaParameterTypesEnum::INTEGER,
        'string' => SchemaParameterTypesEnum::STRING,
        'bool' => SchemaParameterTypesEnum::BOOLEAN,
        'boolean' => SchemaParameterTypesEnum::BOOLEAN,
        'array' => SchemaParameterTypesEnum::ARRAY,
    ];

    private const DIGITS = 'digits';
    private const MIN_DIGITS = 'min_digits';
    private const MAX_DIGITS = 'max_digits';
    private const MIN = 'min';
    private const MAX = 'max';

    public static function build(array $ruleConditions): self
    {
        $type = null;
        $minLength = null;
        $maxLength = null;
        $min = null;
        $max = null;

        foreach ($ruleConditions as $ruleCondition) {
            if (isset(self::TYPE_MAPPING[$ruleCondition])) {
                $type = self::TYPE_MAPPING[$ruleCondition];
            }

            if (null === $type) {
                $type = SchemaParameterTypesEnum::STRING;
            }

            if (Str::contains($ruleCondition, [
                self::DIGITS . ':',
                self::MIN_DIGITS . ':',
                self::MAX_DIGITS . ':',
                self::MIN . ':',
                self::MAX . ':',
            ])) {
                [$alias, $value] = explode(':', $ruleCondition);

                switch ($alias) {
                    case self::DIGITS:
                        $min = $value;
                        $max = $value;
                        break;

                    case self::MIN_DIGITS:
                        $minLength = $value;
                        break;

                    case self::MAX_DIGITS:
                        $maxLength = $value;
                        break;

                    case self::MIN:
                        if ($type === SchemaParameterTypesEnum::STRING) {
                            $minLength = $value;
                        } elseif (
                            $type === SchemaParameterTypesEnum::INTEGER ||
                            $type === SchemaParameterTypesEnum::NUMBER
                        ) {
                            $min = $value;
                        }
                        break;

                    case self::MAX:
                        if ($type === SchemaParameterTypesEnum::STRING) {
                            $maxLength = $value;
                        } elseif (
                            $type === SchemaParameterTypesEnum::INTEGER ||
                            $type === SchemaParameterTypesEnum::NUMBER
                        ) {
                            $max = $value;
                        }
                        break;

                }
            }
        }

        return self::run()
            ->setType($type)
//            ->setDescription('test')
//            ->setExample('test')
            ->setMinimum($min)
            ->setMaximum($max)
            ->setMinLength($minLength)
            ->setMaxLength($maxLength);
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getExample(): null|float|array|int|string
    {
        return $this->example;
    }

    public function setExample(null|float|array|int|string $example): self
    {
        $this->example = $example;
        return $this;
    }
}
