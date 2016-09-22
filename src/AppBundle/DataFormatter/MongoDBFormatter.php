<?php

namespace AppBundle\DataFormatter;

class MongoDBFormatter extends AbstractFormatter
{
    /**
     * A map of types to format classes
     *
     * @var array
     */
    protected $types = array(
        'ObjectId'  => '\MongoId',
        'ISODate'   => '\MongoDate',
    );

    /**
     * Formats raw (array) data into proper data types. Is recursive.
     * Note: all empty strings will be cast as null.
     *
     * @param  array $data          The array data to format
     * @param  array $formattedData The formatted data (for recursive purposes)
     * @return array The formatted data
     */
    public function formatRaw(array $data, array $patterns = [], array $conditions = [], array $formattedData = array())
    {
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $v = trim($v);
                if (is_numeric($v)) {
                    // Numeric string value, convert
                    if (preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $v)) {
                        // Convert to float
                        $v = (float) $v;
                    } else {
                        // Convert to integer
                        $v = (int) $v;
                    }
                } else {
                    // Non-numeric string, convert bools, nulls, and empty strings
                    $value = strtolower($v);
                    switch ($value) {
                        case 'null':
                            $v = null;
                            break;
                        case 'true':
                            $v = true;
                            break;
                        case 'false':
                            $v = false;
                            break;
                        case '':
                            $v = null;
                            break;
                        default:
                            $v = $this->convertTypes($v);
                            $v = $this->matchPatterns($v, $patterns);
                            break;
                    }
                }
            } elseif (is_array($v)) {
                // Recursively format sub-objects
                $formattedData[$k] = array();
                $v = $this->formatRaw($v, $patterns, $conditions, $formattedData[$k]);
            }
            $formattedData[$k] = $this->matchConditions($v, $conditions);
        }
        return $formattedData;
    }

    /**
     * Replaces values using pattern filters.
     *
     * @param   mixed   $value
     * @param   array   $patterns
     * @return  mixed
     */
    protected function matchPatterns($value, array $patterns)
    {
        foreach ($patterns as $key) {
            if (false === $this->hasPatternFilter($key)) {
                continue;
            }
            $value = $this->getPatternFilter($key)->replace($value);
        }
        return $value;
    }

    /**
     * Replaces values using condition filters.
     *
     * @param   mixed   $value
     * @param   array   $patterns
     * @return  mixed
     */
    protected function matchConditions($value, array $conditions)
    {
        foreach ($conditions as $key) {
            if (false === $this->hasConditionFilter($key)) {
                continue;
            }
            $value = $this->getConditionFilter($key)->replace($value);
        }
        return $value;
    }

    /**
     * Converts a value to a type, using a type class
     * @see $this->types for mappings
     *
     * @param  string $value The value to convert
     * @return mixed The converted value
     */
    protected function convertTypes($value)
    {
        foreach ($this->types as $type => $typeClass) {
            $formatterName = $type . '::';
            if (substr($value, 0, strlen($formatterName)) === $formatterName) {
                $value = str_replace($formatterName, '', $value);
                if ($type == 'ISODate') {
                    $value = new $typeClass(strtotime($value));
                } else {
                    $value = new $typeClass($value);
                }
            }
        }
        return $value;
    }
}
