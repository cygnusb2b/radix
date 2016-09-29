<?php

namespace AppBundle\Utility;

use AppBundle\Exception\HttpFriendlyException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Static utility class for common request functions.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class RequestUtility
{
    /**
     * Extracts the customer creation payload from the request.
     *
     * @param   Request $request
     * @param   bool    $flat       Whether to return with/without the data member.
     * @return  array
     * @throws  HttpFriendlyException
     */
    public static function extractPayload(Request $request, $flat = true)
    {
        if (0 !== stripos($request->headers->get('content-type'), 'application/json')) {
            throw new HttpFriendlyException('Invalid request content type. Expected application/json.', 415);
        }
        // JSON request.
        $payload = @json_decode($request->getContent(), true);
        if (!isset($payload['data'])) {
            throw new HttpFriendlyException('No data member was found in the request payload.', 422);
        }

        $payload['data'] = (array) $payload['data'];

        return (true == $flat) ? $payload['data'] : $payload;
    }

    /**
     * Notifies New Relic of an Exception.
     *
     * @param \Exception $exception
     */
    public static function notifyException(\Exception $exception)
    {
        if (extension_loaded('newrelic')) {
            newrelic_notice_error($exception->getMessage(), $exception);
            newrelic_add_custom_parameter('file', $exception->getFile());
            newrelic_add_custom_parameter('line', $exception->getLine());
        }
    }

    /**
     * Parses flat `target:field.path` keys into an associative array.
     *
     * @param   array   $data
     * @return  array
     */
    public static function parsePayloadData(array $data)
    {
        $parsed    = [];
        $parsePath = function($path, $value, &$parsed) {
            $keys = explode('.', $path);
            foreach ($keys as $key) {
                $parsed = &$parsed[$key];
            }
            $parsed = $value;
        };

        foreach ($data as $key => $value) {
            $value = trim($value);
            if (empty($value)) {
                // Skip empty values.
                continue;
            }
            $parts = explode(':', $key);
            if (2 !== count($parts)) {
                throw new HttpFriendlyException('The form submission format is invalid. Expected a field keys formatted as [target]:[fieldPath.fieldName].', 422);
            }
            $target = $parts[0];
            $path   = $parts[1];

            if (!isset($parsed[$target])) {
                $parsed[$target] = [];
            }

            $parsedValue = null;
            $parsePath($path, $value, $parsedValue);

            if (!isset($parsed[$target])) {
                $parsed[$target] = [];
            }
            $parsed[$target] = array_merge_recursive($parsed[$target], $parsedValue);
        }
        return $parsed;
    }
}
