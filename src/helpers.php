<?php

if (!function_exists('is_enum')) {
    function is_enum($object): bool
    {
        return is_object($object) && enum_exists(get_class($object));
    }
}
