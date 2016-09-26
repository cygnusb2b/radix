<?php

namespace AppBundle\Utility;

/**
 * Generic PHP helpers.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class HelperUtility
{
    /**
     * Determines if the string value is formatted as a MongoId.
     *
     * @param   string  $value
     * @return  bool
     */
    public static function isMongoIdFormat($value)
    {
        return 1 === preg_match('/^[a-f0-9]{24}$/', $value);
    }

    /**
     * Determines if the value is set and is an array.
     *
     * @param   mixed   $value
     * @param   string  $key
     * @return  bool
     */
    public static function isSetArray($value, $key)
    {
        return (isset($value[$key]) && is_array($value[$key]));
    }

    /**
     * Determines if the value is set and is not empty
     *
     * @param   mixed   $value
     * @param   string  $key
     * @return  bool
     */
    public static function isSetNotEmpty($value, $key)
    {
        return (isset($value[$key]) && !empty($value[$key]));
    }
}
