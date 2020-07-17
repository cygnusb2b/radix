<?php

namespace AppBundle\DataFormatter;

use AppBundle\DataFormatter\Filters\PatternFilter;
use AppBundle\DataFormatter\Filters\ConditionFilter;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * An array of regex match patterns to callable formatters.
     *
     * @var PatternFilter[]
     */
    protected $patternFilters = [];

    /**
     * An array of numeric conditions to callable formatters.
     *
     * @var array
     */
    protected $conditionFilters = [];

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->initializePatternFilters();
        $this->initializeConditionFilters();
    }

    /**
     * Formats a ParameterBag object and returns it
     *
     * @param  ParameterBag $data The data to format
     * @param  array        $regexes
     * @return ParameterBag The formatted data
     */
    public function format(ParameterBag $data, array $patterns = [], array $conditions = [])
    {
        $formatted = $this->formatRaw($data->all(), $patterns, $conditions);
        $data->replace($formatted);
        return $data;
    }

    /**
     * Formats from an array and returns it
     * Is a proxy for @see format()
     *
     * @param  array $data The data to format
     * @param  array $patterns
     * @return ParameterBag The formatted data
     */
    public function formatFromArray(array $data, array $patterns = [], array $conditions = [])
    {
        return $this->format(new ParameterBag($data), $patterns, $conditions);
    }

    /**
     * Formats raw (array) data into proper data types. Is recursive.
     *
     * @param  array $data          The array data to format
     * @param  array $formattedData The formatted data (for recursive purposes)
     * @return array The formatted data
     */
    abstract public function formatRaw(array $data, array $patterns = [], array $conditions = [], array $formattedData = array());

    /**
     * Registers a pattern filter.
     *
     * @param   PatternFilter   $filter
     * @return  self
     */
    public function addPatternFilter(PatternFilter $filter)
    {
        $this->patternFilters[$filter->getKey()] = $filter;
        return $this;
    }

    /**
     * Gets a registered pattern filter.
     *
     * @param   string              $key
     * @return  PatternFilter|null
     */
    public function getPatternFilter($key)
    {
        if (isset($this->patternFilters[$key])) {
            return $this->patternFilters[$key];
        }
        return null;
    }

    /**
     * Determines if a pattern filter is registered.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasPatternFilter($key)
    {
        return null !== $this->getPatternFilter($key);
    }

    /**
     * Gets a registered condition filter.
     *
     * @param   string              $key
     * @return  PatternFilter|null
     */
    public function getConditionFilter($key)
    {
        if (isset($this->conditionFilters[$key])) {
            return $this->conditionFilters[$key];
        }
        return null;
    }

    /**
     * Determines if a condition filter is registered.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasConditionFilter($key)
    {
        return null !== $this->getConditionFilter($key);
    }

    /**
     * Registers a condition filter.
     *
     * @param   ConditionFilter     $filter
     * @return  self
     */
    public function addConditionFilter(ConditionFilter $filter)
    {
        $this->conditionFilters[$filter->getKey()] = $filter;
        return $this;
    }

    /**
     * Initializes built-in pattern filters
     *
     * @return  self
     */
    protected function initializePatternFilters()
    {
        // Mongo Date objects
        $this->addPatternFilter(
            PatternFilter::create(
                'mongoDate',
                [
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{1,3}$/',
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$/',
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{1,3}Z$/',
                    '/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\+[0-9]{4}|\-[0-9]{4})$/',
                ],
                function ($value) {
                    return new \MongoDate(strtotime($value));
                }
            )
        );

        // Mongo ID objects
        $this->addPatternFilter(
            PatternFilter::create(
                'mongoId',
                '/^[a-f0-9]{24}$/i',
                function ($value) {
                    return new \MongoId($value);
                }
            )
        );
        return $this;
    }

    /**
     * Initializes built-in condition filters
     *
     * @return  self
     */
    protected function initializeConditionFilters()
    {
        $this->addConditionFilter(
            ConditionFilter::create(
                'mongoInt64',
                ['integer', 'double'],
                function ($value) {
                    return (is_integer($value) || (is_numeric($value) && floor($value) == $value)) ? new \MongoInt64($value) : $value;
                }
            )
        );
    }
}
