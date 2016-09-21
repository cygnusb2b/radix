<?php

namespace AppBundle\Exception;

use \Exception;

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
