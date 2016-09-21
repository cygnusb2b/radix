<?php

namespace AppBundle\Exception;

use \Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class HttpFriendlySerializer
{
    /**
     * @var bool
     */
    private $showMeta;

    /**
     * @param   bool    $showMeta
     */
    public function __construct($showMeta = false)
    {
        $this->enableMeta($showMeta);
    }

    /**
     * Extracts the appropriate HTTP status code for the provided exception.
     *
     * @param   Exception   $exception
     * @return  int
     */
    public function extractStatusCode(Exception $exception)
    {
        if ($exception instanceof HttpFriendlyException || $exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }
        if ($exception instanceof ExceptionQueue) {
            return $this->extractStatusCodeForQueue($exception->all());
        }
        return 500;
    }

    /**
     * Extracts the appropriate HTTP status code for the provided queue of exceptions.
     *
     * @param   Exception   $exception
     * @return  int
     */
    public function extractStatusCodeForQueue(array $exceptions)
    {
        $codes = [];
        foreach ($exceptions as $e) {
            $code = $this->extractStatusCode($e);
            if (isset($codes[$code])) {
                $codes[$code]++;
            } else {
                $codes[$code] = 1;
            }
        }
        arsort($codes);
        $codes = array_keys($codes);
        return array_shift($codes);
    }

    /**
     * Enables/disables displaying meta details in the response.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function enableMeta($bit = true)
    {
        $this->showMeta = (bool) $bit;
        return $this;
    }

    /**
     * Converts an exception to an HTTP friendly array.
     *
     * @param   Exception   $exception
     * @return  array
     */
    public function toArray(Exception $exception)
    {
        if ($exception instanceof ExceptionQueue) {
            return $this->queueToArray($exception->all());
        }
        if ($exception instanceof HttpFriendlyException) {
            $error = [
                'status'    => (string) $exception->getStatusCode(),
                'title'     => $exception->getMessage(),
                'detail'    => $exception->getDetail(),
            ];
        } elseif ($exception instanceof HttpExceptionInterface) {
            $error = [
                'status'    => (string) $exception->getStatusCode(),
                'title'     => Response::$statusTexts[$exception->getStatusCode()],
                'detail'    => $exception->getMessage(),
            ];
        } else {
            $error = [
                'status'    => '500',
                'title'     => get_class($exception),
                'detail'    => $exception->getMessage(),
            ];
        }
        if (true === $this->showMeta) {
            $error['meta'] = [
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                'trace'     => $exception->getTrace(),
            ];
        }
        return $error;
    }

    /**
     * Converts an exception to an HTTP friendly JSON string.
     *
     * @param   Exception   $exception
     * @return  string
     */
    public function toJson(Exception $exception)
    {
        return json_encode($this->toArray($exception));
    }

    /**
     * Converts a queue of exceptions into an HTTP friendly array.
     *
     * @param   Exception   $exception
     * @return  array
     */
    public function queueToArray(array $exceptions)
    {
        $errors = [];
        foreach ($exceptions as $e) {
            $errors[] = $this->toArray($e);
        }
        return ['errors' => $errors];
    }

    /**
     * Converts a queue of exceptions into an HTTP friendly JSON string.
     *
     * @param   Exception   $exception
     * @return  string
     */
    public function queueToJson(array $exceptions)
    {
        $process = [];
        foreach ($exceptions as $e) {
            if ($e instanceof ExceptionQueue) {
                foreach ($e->all() as $child) {
                    $process[] = $child;
                }
            } else {
                $process[] = $e;
            }
        }
        return json_encode($this->queueToArray($process));
    }
}
