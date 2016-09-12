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

    /**
     * Gets the list of supported form question answer types.
     *
     * @param   bool    $asObjects
     * @return  array
     */
    public static function getFormAnswerTypes($asObjects = false)
    {
        $types = [
            'string'            => 'A short, open-ended text answer (single line)',
            'textarea'          => 'A long, open-ended text answer (multiple lines)',
            'choice-single'     => 'A list of choices with a single answer',
            'choice-multiple'   => 'A list of choices with multiple answers',
            'country'           => 'A list of countries with a single answer',
            'region'            => 'A list of regions (states, provinces, etc) with a single answer',
            'password'          => 'A password answer',
            'email'             => 'An email address answer',
            'url'               => 'A url/website answer',
            'boolean'           => 'A yes or no answer (boolean)',
            'datetime'          => 'A date answer with time',
            'date'              => 'A date answer without time',
            'month'             => 'A date answer with year and month only',
            'year'              => 'A date answer with year only',
            'time'              => 'A time answer',
            'tel'               => 'A telephone number answer',
            'integer'           => 'A number answer without decimals (integer)',
            'float'             => 'A number answer with decimals (float)',

        ];
        if (false == $asObjects) {
            return $types;
        }
        $objects = [];
        foreach ($types as $key => $value) {
            $objects[] = ['value' => $key, 'label' => $value];
        }
        return $objects;
    }

    /**
     * Gets the list of supported simple schedule types.
     *
     * @param   bool    $asObjects
     * @return  array
     */
    public static function getSimpleScheduleTypes($asObjects = false)
    {
        $types = [
            'hourly'    => 'Hourly',
            'daily'     => 'Daily',
            'weekly'    => 'Weekly',
            'monthly'   => 'Monthly',
        ];
        if (false == $asObjects) {
            return $types;
        }
        $objects = [];
        foreach ($types as $key => $value) {
            $objects[] = ['value' => $key, 'label' => $value];
        }
        return $objects;
    }
}
