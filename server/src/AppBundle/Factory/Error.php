<?php

namespace AppBundle\Factory;

use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Models\Model;

/**
 * Contains factory error information.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class Error
{
    private $reason;

    private $httpStatusCode;

    public function __construct($reason, $httpStatusCode = 400)
    {
        $this->reason = $reason;
        $this->httpStatusCode = (int) $httpStatusCode;
    }

    public function getException()
    {
        return new HttpFriendlyException($this->reason, $this->httpStatusCode);
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function throwException()
    {
        throw $this->getException();
    }
}
