<?php

namespace AppBundle\DataFormatter\Filters;

class ConditionFilter implements FilterInterface
{
    protected $key;

    protected $dataTypes = [];

    protected $closure;

    public function __construct($key, $dataTypes, \Closure $closure)
    {
        $this->key = $key;
        $this->dataTypes = (Array) $dataTypes;
        $this->closure = $closure;
    }

    public static function create($key, $dataTypes, \Closure $closure)
    {
        return new self($key, $dataTypes, $closure);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function replace($value)
    {
        $closure = $this->closure;
        foreach ($this->dataTypes as $type) {
            if ($type === gettype($value)) {
                return $closure($value);
            }
        }
        return $value;
    }
}
