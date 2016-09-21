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
     * @return  array
     * @throws  HttpFriendlyException
     */
    public static function extractPayload(Request $request)
    {
        if (0 !== stripos($request->headers->get('content-type'), 'application/json')) {
            throw new HttpFriendlyException('Invalid request content type. Expected application/json.', 415);
        }
        // JSON request.
        $payload = @json_decode($request->getContent(), true);
        if (!isset($payload['data'])) {
            throw new HttpFriendlyException('No data member was found in the request payload.', 422);
        }
        return (array) $payload['data'];
    }
}
