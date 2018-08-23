<?php

namespace AppBundle\Exception;

use \Exception;

/**
 * @todo    This class needs to be updated to support internal Symfony exception handling.
 */
class ExceptionQueue extends Exception
{
    /**
     * @var Exception[]
     */
    private $exceptions = [];

    /**
     * @param   Exception   $e
     * @return  self
     */
    public function add(Exception $e)
    {
        $this->exceptions[] = $e;
        return $this;
    }

    /**
     * @return  Exception[]
     */
    public function all()
    {
        return $this->exceptions;
    }

    /**
     * @return  bool
     */
    public function isEmpty()
    {
        return empty($this->exceptions);
    }

}
