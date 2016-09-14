<?php

namespace AppBundle\Utility;

use As3\Modlr\Models\AbstractModel;

/**
 * Static utility class for common model functions.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ModelUtility
{
    /**
     * Formats an external url value to ensure http: is appended (when applicable).
     *
     * @param   string  $url
     * @return  string|null
     */
    public static function formatExternalUrlValue($url)
    {
        $url = trim($url);
        if (0 === preg_match('/^http:|^https:|^mailto:|^ftp:|^\/\//i', $url)) {
            // Assume http:
            $url = sprintf('http://%s', $url);
        }
        $url = rtrim($url, '/');
        return (empty($url)) ? null : $url;
    }

    /**
     * Gets a model value for the provided path (dot-notated).
     *
     * @param   AbstractModel   $model
     * @param   string          $path
     * @return  mixed
     */
    public static function getModelValueFor(AbstractModel $model, $path)
    {
        $keys    = explode('.', $path);
        $current = array_shift($keys);

        if (empty($keys)) {
            return $model->get($current);
        } else {
            $value = $model->get($current);
            if ($value instanceof AbstractModel) {
                return self::getModelValueFor($value, implode('.', $keys));
            }
        }
    }

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
            'text'              => 'A short, open-ended text answer (single line)',
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

    /**
     * Gets the list of supported form question answer types.
     *
     * @param   bool    $asObjects
     * @return  array
     */
    public static function getQuestionChoiceTypes($asObjects = false)
    {
        $types = [
            'standard' => 'A standard choice.',
            'other'    => 'An other choice.',
            'none'     => 'A none-of-the-above choice.',
            'hidden'   => 'A hidden (internal) choice.',

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
