<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocStringValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocStringParserService
{
    /**
     * Ищем все символы между docstring включительно
     * /\*\* - начало (/**)
     * [\s\S]* - любой символ
     * \*\/ - конец
     */
    private const DOC_STRING_REGEX = '#/\*\*[\s\S]*\*/#';

    /**
     * @ - начинается с собаки
     * ([\s\S]*?) - ленивая загрузка любого символа
     * (?=\s*@|\s*\*\/) - окончание совпадения ЛИБО @ ЛИБО *\/ (НЕВКЛЮЧИТЕЛЬНО)
     */
    private const DOC_STRING_PARAMETER_REGEX = '#@([\s\S]*?)(?=\s*@|\s*\*/)#';

    /**
     * @ - начинается с собаки
     * \w - любые буквы (минимум 1)
     * (?= ) - оканчивается пробелом (НЕВКЛЮЧИТЕЛЬНО)
     */
    private const DOC_STRING_PARAMETER_NAME_REGEX = '#@\w+(?= )#';

    public function parse(string $fileContents): DocStringValue
    {
        $apiGroup = null;
        $apiDescription = null;

        $docStringData = Str::match(self::DOC_STRING_REGEX, $fileContents);
        if (Str::length($docStringData) > 0) {
            $docStringParameters = Str::matchAll(self::DOC_STRING_PARAMETER_REGEX, $docStringData);
            foreach ($docStringParameters as $key => $docStringParameter) {
                $parameter = "@$docStringParameter";
                $parameterName = Str::match(self::DOC_STRING_PARAMETER_NAME_REGEX, $parameter);
                $parameter = Str::remove("$parameterName ", $parameter);
                $parts = explode("\n", $parameter);
                $parts = Arr::map($parts, fn(string $part) => self::removeRowPrefix($part));
                $parts = Arr::whereNotNull($parts);
                $docStringParameters[$parameterName] = implode("\n", $parts);
                $docStringParameters[$key] = null;
            }
            $docStringParameters = Arr::whereNotNull($docStringParameters->toArray());
            dd($docStringParameters);
        }

        return DocStringValue::run()
            ->setApiGroup($apiGroup)
            ->setApiDescription($apiDescription);
    }

    private static function removeRowPrefix(string $string): ?string
    {
        if (Str::startsWith($string, ' ')) {
            $string = Str::substr($string, 1, Str::length($string) - 1);
        }

        if (Str::startsWith($string, '*')) {
            $string = Str::substr($string, 1, Str::length($string) - 1);
        }

        if (Str::startsWith($string, ' ')) {
            $string = Str::substr($string, 1, Str::length($string) - 1);
        }

        if ($string === '') {
            $string = null;
        }

        return $string;
    }
}
