<?php

if (!function_exists('is_enum')) {
    function is_enum($object): bool
    {
        return is_object($object) && enum_exists(get_class($object));
    }
}

if (! function_exists('array_filter_not_null')) {
    function array_filter_not_null(array $array): array
    {
        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }
}
