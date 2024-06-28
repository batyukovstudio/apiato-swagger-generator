<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract;

use Batyukovstudio\ApiatoSwaggerGenerator\Contracts\NotNullFilterableValue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Value implements Arrayable
{
    public static function run(): static
    {
        $static = new static();
        $static->mount();

        return $static;
    }

    public function toArray(): array
    {
        $result = [];
        $vars = get_object_vars($this);

        foreach ($vars as $name => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif (is_object($value) && property_exists($value, 'value')) {
                $value = $value->value;
            } elseif (is_iterable($value)) {
                foreach ($value as $valueItemKey => $valueItem) {
                    if ($valueItem instanceof Arrayable){
                        $valueItem = $valueItemKey->toArray();
                    }
                    $value[$valueItemKey] = $valueItem;
                }
            }

            $result[$name] = $value;
        }

        if ($this instanceof NotNullFilterableValue) {
            $result = array_filter_not_null($result);
        }

        return $result;
    }

    protected function mount(): void {}
}