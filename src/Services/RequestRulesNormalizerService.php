<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Illuminate\Support\Collection;

class RequestRulesNormalizerService
{
    public function normalize(array $rules): Collection
    {
        $normalized = new Collection();

        foreach ($rules as $ruleName => $ruleConditions) {

            if (is_string($ruleConditions)) {
                $normalized[$ruleName] = self::normalizeStringRuleConditions($ruleConditions);
            } elseif (is_array($ruleConditions)) {
                $normalized[$ruleName] = self::normalizeArrayRuleConditions($ruleConditions);
            } else {
                $normalized[$ruleName] = self::normalizeObjectRuleCondition($ruleConditions);
            }
        }

        return $normalized;
    }

    private static function normalizeStringRuleConditions(string $conditions): array
    {
        return explode('|', $conditions);
    }

    private static function normalizeArrayRuleConditions(array $conditions): array
    {
        $normalized = [];

        foreach ($conditions as $condition) {
            if (is_string($condition)) {
                $new = self::normalizeStringRuleConditions($condition);
            } else {
                $new = self::normalizeObjectRuleCondition($condition);
            }

            $normalized = array_merge($normalized, $new);
        }

        return $normalized;
    }

    private static function normalizeObjectRuleCondition(object $condition): array
    {
        return [$condition::class];
    }
}
