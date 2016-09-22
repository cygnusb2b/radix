<?php

namespace AppBundle\DataFormatter;

use Symfony\Component\HttpFoundation\ParameterBag;

interface FormatterInterface
{
    /**
     * Formats a ParameterBag object and returns it
     *
     * @param  ParameterBag $data The data to format
     * @return ParameterBag
     */
    public function format(ParameterBag $data);

    /**
     * Formats from an array and returns it
     * Is a proxy for @see format()
     *
     * @param  array $data The data to format
     * @return ParameterBag
     */
    public function formatFromArray(array $data);

    /**
     * Formats raw (array) data into proper data types. Is recursive.
     *
     * @param  array $data          The array data to format
     * @param  array $formattedData The formatted data (for recursive purposes)
     */
    public function formatRaw(array $data, array $formattedData = array());
}
