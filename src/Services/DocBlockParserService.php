<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiDescriptionValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiGroupValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiSummaryValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\DocBlockValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\DocBockTagsEnum;
use phpDocumentor\Reflection\DocBlockFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocBlockParserService
{
    /**
     * Ищем все символы между docstring включительно
     * /\*\* - начало (/**)
     * [\s\S]* - любой символ
     * \*\/ - конец
     */
    private const DOC_STRING_REGEX = '#/\*\*[\s\S]*\*/#';

    public function parse(string $fileContents): ?DocBlockValue
    {
        $apiGroup = null;
        $apiSummary = null;
        $apiDescription = null;

        /** @var phpDocumentor\Reflection\DocBlock\Description $tagDescription */
        $docblock = self::extractDocBlock($fileContents);
        if (null !== $docblock) {
            foreach ($docblock->getTags() as $tag) {
                $tagName = $tag->getName();
                $tagDescription = (string) $tag->getDescription();  // (string) обязательно, тут перегрузка оператора

                switch ($tagName) {

                    case DocBockTagsEnum::API_GROUP->value:
                        $apiGroup = ApiGroupValue::run()
                            ->setGroupName($tagDescription);

                    case DocBockTagsEnum::API_SUMMARY->value:
                        $apiSummary = ApiSummaryValue::run()
                            ->setText($tagDescription);

                    case DocBockTagsEnum::API_DESCRIPTION->value:
                        $apiDescription = ApiDescriptionValue::run()
                            ->setText($tagDescription);
                }
            }
        }

        $docBlock = null;

        if ($apiGroup !== null ||
            $apiSummary !== null ||
            $apiDescription !== null
        ) {
            $docBlock = DocBlockValue::run()
                ->setApiGroup($apiGroup)
                ->setApiSummary($apiSummary)
                ->setApiDescription($apiDescription);
        }

        return $docblock;
    }

    private static function extractDocBlock(string $fileContents): ?phpDocumentor\Reflection\DocBlock
    {
        $docBlock = null;

        $docBlockText = Str::match(self::DOC_STRING_REGEX, $fileContents);
        if (Str::length($docBlockText) > 0) {
            /** @var phpDocumentor\Reflection\DocBlock $docblock */
            $factory = DocBlockFactory::createInstance();
            $docblock = $factory->create($docBlockText);
        }

        return $docBlock;
    }
}
