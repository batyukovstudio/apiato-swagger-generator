<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiDescriptionValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiGroupValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\ApiSummaryValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks\DocBlockValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Enums\DocBlocks\DocBockTagsEnum;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock;
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
        $docBlock = self::extractDocBlock($fileContents);
        if (null !== $docBlock) {
            foreach ($docBlock->getTags() as $tag) {
                $tagName = $tag->getName();
                $tagDescription = (string) $tag->getDescription();  // (string) обязательно, тут перегрузка оператора

                switch ($tagName) {

                    case DocBockTagsEnum::API_GROUP->value:
                        $apiGroup = ApiGroupValue::run()
                            ->setGroupName($tagDescription);
                        break;

                    case DocBockTagsEnum::API_SUMMARY->value:
                        $apiSummary = ApiSummaryValue::run()
                            ->setText($tagDescription);
                        break;

                    case DocBockTagsEnum::API_DESCRIPTION->value:
                        $apiDescription = ApiDescriptionValue::run()
                            ->setText($tagDescription);
                        break;
                }
            }
        }

        $docBlockTagsCount = count(Arr::whereNotNull([
            $apiGroup,
            $apiSummary,
            $apiDescription,
        ]));

        $docBlock = null;
        if ($docBlockTagsCount > 0) {
            $docBlock = DocBlockValue::run()
                ->setApiGroup($apiGroup)
                ->setApiSummary($apiSummary)
                ->setApiDescription($apiDescription);
        }

        return $docBlock;
    }

    private static function extractDocBlock(string $fileContents): ?DocBlock
    {
        $docBlock = null;

        $docBlockText = Str::match(self::DOC_STRING_REGEX, $fileContents);
        if (Str::length($docBlockText) > 0) {
            if (false === Str::contains($docBlockText, "\n//")) {
                /** @var DocBlock $docBlock */
                $factory = DocBlockFactory::createInstance();
                $docBlock = $factory->create($docBlockText);
            }
        }

        return $docBlock;
    }
}
