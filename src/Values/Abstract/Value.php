<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract;

use Illuminate\Contracts\Support\Arrayable;
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
            } elseif (is_array($value)) {
                foreach ($value as $valueItemKey => $valueItem) {
                    if ($valueItem instanceof Arrayable) {
                        $value[$valueItemKey] = $valueItem->toArray();
                    }
                }
            }

            $result[$name] = $value;
//            $result[Str::snake((string)$name)] = $value;
        }

        return $result;
    }

    protected function mount(): void {}
}