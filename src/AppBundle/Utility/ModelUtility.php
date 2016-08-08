<?php

namespace AppBundle\Utility;

/**
 * Static utility class for common model functions.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ModelUtility
{
    /**
     * Creates a URL safe, dasherized string from the provided value.
     *
     * @param   string  $value
     * @return  string
     */
    public static function sluggifyValue($value)
    {
        $value = html_entity_decode($value);
        $value = strip_tags($value);
        $value = str_replace(['&', '@'], [' and ', ' at '], $value);
        $value = str_replace(['-', '_'], ' ', $value);
        $value = trim(strtolower(preg_replace('/[^A-Za-z0-9 ]+/', '', $value)));

        $parts = explode(' ', $value);
        foreach ($parts as $i => $part) {
            $part = trim($part);
            if (empty($part)) {
                unset($parts[$i]);
            }
        }
        return implode('-', $parts);
    }
}
