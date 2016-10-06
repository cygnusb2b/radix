<?php

namespace AppBundle\Exception;

use As3\Parameters\Parameters;
use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;

class HttpFriendlyException extends BaseHttpException
{
    /**
     * @param   string          $detail
     * @param   int             $statusCode
     * @param   int             $code
     * @param   Exception|null  $exception
     */
    public function __construct($detail, $statusCode, array $meta = [], \Exception $previous = null, array $headers = [], $code = 0)
    {
        foreach ($meta as $key => $value) {
            $key = sprintf('radix.%s', $key);
            $headers[$key] = $value;
        }
        parent::__construct($statusCode, $detail, $previous, $headers, $code);
    }
}
