<?php

namespace AppBundle\Exception;

use As3\Parameters\Parameters;
use Symfony\Component\HttpFoundation\Response;

use \Exception;

class HttpFriendlyException extends Exception
{
    /**
     * @var Parameters
     */
    private $meta;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $detail;

    /**
     * @param   string          $detail
     * @param   int             $statusCode
     * @param   int             $code
     * @param   Exception|null  $exception
     */
    public function __construct($detail, $statusCode, array $meta = [], $code = 0, Exception $previous = null)
    {
        $codes      = Response::$statusTexts;
        $statusCode = (int) $statusCode;
        if (!isset($codes[$statusCode])) {
            $statusCode = 500;
        }

        parent::__construct($codes[$statusCode], $code, $previous);

        $this->setDetail($detail);
        $this->statusCode = $statusCode;
        $this->meta       = new Parameters($meta);
    }

    /**
     * @return  Parameters
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return  string|null
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @return  int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the detailed message.
     *
     * @param   string  $detail
     * @return  self
     */
    private function setDetail($detail)
    {
        $detail = (string) $detail;
        $this->detail = empty($detail) ? null : $detail;
        return $this;
    }
}
